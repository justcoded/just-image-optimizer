<?php

namespace jcf\migrations;

use jcf\models\Settings;

/**
 * Class m3x000
 * Migration from v2.3 to v3.0. A lot of changes were made:
 * DB:
 *      settings stored the same as file system in 2 keys: jcf-fields, jcf-fieldsets
 *
 * Deprecated fields:
 *      Upload Media, Fields group are deprecated and have been removed.
 *      Fields replaced with "Collections" with new slug.
 *      All data which is possible to move will be migrated into new meta key / slug.
 *
 * @package jcf\migrations
 */
class m3x000 extends \jcf\core\Migration
{
	protected $version = '3.000';
	protected $unique = 0;

	/**
	 * Read data from storage
	 */
	protected function readData()
	{
		$fields = array();
		$fieldsets = array();

		// read from DB
		if ( $this->isDataSource( Settings::CONF_SOURCE_DB ) ) {
			$post_types = jcf_get_post_types();
			foreach ( $post_types as $post_type => $object ) {
				$p_fieldsets = $this->readDB("jcf_fieldsets-{$post_type}");
				if ( empty($p_fieldsets) ) continue;

				$p_fields = $this->readDB("jcf_fields-{$post_type}");
				if ( empty($p_fields) ) $p_fields = array();

				$fields[$post_type] = $p_fields;
				$fieldsets[$post_type] = $p_fieldsets;
			}
		}
		// read from FS
		else {
			$json = $this->readFS('jcf-settings/jcf_settings.json');
			$data = json_decode($json, true);

			if ( !empty($data['fieldsets']) ) {
				foreach ($data['fieldsets'] as $post_type => $fieldsets_array) {
					if ( !empty($fieldsets_array) ) {
						$fieldsets[$post_type] = $fieldsets_array;
						$fields[$post_type] = array();
					}
				}

				if ( !empty($data['field_settings']) ) {
					foreach ($data['field_settings'] as $post_type => $fields_array) {
						if ( !empty($fields_array) ) {
							$fields[$post_type] = $fields_array;
						}
					}
				}
			}
		}

		$this->data = array(
			self::FIELDS_KEY => $fields,
			self::FIELDSETS_KEY => $fieldsets,
		);
	}

	/**
	 * Test compatibility and deprecated fields
	 *
	 * @return bool
	 */
	protected function test()
	{
		if ( ! $deprecated = $this->findDeprecatedFields() ) {
			return false;
		}

		$warnings = array();
		$post_types = get_post_types(array(), 'objects');
		$not_registered_post_types = false;

		foreach ($deprecated as $post_type => $fields) {
			$pt_title = ucfirst($post_type) . '*';
			if ( isset($post_types[$post_type]) ) {
				$pt = $post_types[$post_type];
				$pt_title = $pt->label;
			}
			else {
				$not_registered_post_types = true;
			}

			$field_names = array();
			foreach ($fields as $field) {
				$field_names[] = "{$field['full_title']} ({$field['field_type']})";
			}

			$warnings[] = "<strong>$pt_title</strong> fields <i>" . implode(', ', $field_names) . "</i> will be converted.";
		}

		$html = '
			<p>There are several <strong>deprecated field types</strong> which are no longer exists in a new version: Upload Media, Fields Group.
				They will be replaced with new field type: Collection. <br>
				If you use field shortcodes on your site - they won\'t work anymore and have to be replaced with new code.<br>
				We will try to migrate post data to new format. To prevent frontend errors we will rename new fields and import old data to them.<br>
				<b>You will need to upgrade your templates to read data from new fields/format.</b>
			</p>
			<ul class="jcf_list">
				<li>' . implode('</li><li>', $warnings) . '</li>
			</ul>
			';

		if ($not_registered_post_types) {
			$html .= '<p><small>* Post type is not registered anymore.</small></p>';
		}

		return $html;
	}

	/**
	 * Find deprecated field ids
	 * Upload Media and Fields group can't be added inside collections, so we do not need to check them
	 *
	 * @return array
	 */
	protected function findDeprecatedFields()
	{
		$deprecated = array();
		$all_fields = $this->data[self::FIELDS_KEY];
		$all_fieldsets = $this->data[self::FIELDSETS_KEY];

		foreach ($all_fieldsets as $post_type => $fieldsets) {
			foreach ($fieldsets as $fieldset_id => $fieldset) {
				if ( empty($fieldset['fields']) ) continue;

				foreach ($fieldset['fields'] as $field_id => $dummy_value) {
					// check usual fields
					if ( preg_match('/^(uploadmedia|fieldsgroup)\-/', $field_id, $match) && isset($all_fields[$post_type][$field_id]) ) {
						if ( ! isset( $deprecated[ $post_type ] ) ) {
							$deprecated[ $post_type ] = array();
						}

						$field = $all_fields[$post_type][$field_id];

						$deprecated[$post_type][$field_id] = array(
							'title' => $field['title'],
							'full_title' => $field['title'],
							'field_type' => $match[1],
							'fieldset_id' => $fieldset_id,
						);
					}
				}
			}
		}

		return $deprecated;
	}

	/**
	 * Update fields and fieldsets attributes
	 *
	 * @return boolean
	 */
	protected function update()
	{
		set_time_limit(0);
		$all_fields = $this->data[self::FIELDS_KEY];
		$all_fieldsets = $this->data[self::FIELDSETS_KEY];
		$this->unique = time();

		$existed_fields = array();

		foreach ($all_fieldsets as $post_type => $fieldsets) {
			$existed_fields[$post_type] = array();

			foreach ($fieldsets as $fieldset_id => $fieldset) {

				$fieldset['position'] = 'advanced';
				$fieldset['priority'] = 'default';

				if ( empty($fieldset['fields']) ) {
					$fieldset['fields'] = array();
					$all_fieldsets[$post_type][$fieldset_id] = $fieldset;
					continue;
				}

				foreach ($fieldset['fields'] as $field_id => $dummy_value) {
					if ( ! isset($all_fields[$post_type][$field_id]) ) continue;

					$existed_fields[$post_type][] = $field_id;

					// convert select options
					if ( preg_match('/^(select|selectmultiple)\-/', $field_id, $match) ) {
						$field = $all_fields[$post_type][$field_id];
						$all_fields[$post_type][$field_id] = $this->updateSelectField($field, $post_type);
					}

					// check fieldsgroup and upload media
					if ( preg_match('/^(fieldsgroup|uploadmedia)\-/', $field_id, $match) ) {
						$field = $all_fields[$post_type][$field_id];

						$collection_id = 'collection-' . $this->unique++;

						// generate new collection
						if ( 'fieldsgroup' === $match[1] ){
							$collection = $this->updateFieldsgroup($field, $post_type);
						}
						if ( 'uploadmedia' === $match[1] ){
							$collection = $this->updateUploadmedia($field, $post_type);
						}

						unset($all_fields[$post_type][$field_id]);
						unset($fieldset['fields'][$field_id]);

						$fieldset['fields'][$collection_id] = $collection_id;
						$all_fields[$post_type][$collection_id] = $collection;
						$all_fields[$post_type] = array_merge($all_fields[$post_type], $collection['fields']);

						$existed_fields[$post_type][] = $collection_id;
						$existed_fields[$post_type] = array_merge($existed_fields[$post_type], array_keys($collection['fields']));
					}

					// check collection to record existed fields from collections as well
					if ( preg_match('/^(collection)\-/', $field_id, $match) ) {
						$collection = $all_fields[$post_type][$field_id];
						if ( !empty($collection['fields']) ) {
							$existed_fields[$post_type] = array_merge($existed_fields[$post_type], array_keys($collection['fields']));
						}
					}
				}

				// update fieldset itself
				$all_fieldsets[$post_type][$fieldset_id] = $fieldset;
			}
		}

		// clean up fields and add new keys
		foreach ($all_fields as $post_type => $fields) {
			foreach ($fields as $field_id => $field) {
				// delete missing fields
				if (!isset($existed_fields[$post_type]) || !in_array($field_id, $existed_fields[$post_type]) || empty($field)) {
					unset($all_fields[$post_type][$field_id]);
					continue;
				}
				// add field type key to all fields
				if ( preg_match('/^([a-z0-9]+)\-/i', $field_id, $match) ) {
					$all_fields[$post_type][$field_id]['_type'] = $match[1];
				}
			}
		}

		$this->data = array(
			self::FIELDS_KEY => $all_fields,
			self::FIELDSETS_KEY => $all_fieldsets,
		);

		return true;
	}

	/**
	 * Some old versions has key 'settings', but new component works with key 'options'
	 *
	 * @param array $field
	 * @param string $post_type
	 * @return array
	 */
	protected function updateSelectField( $field, $post_type )
	{
		if ( empty($field['options']) && !empty($field['settings']) ) {
			$field['options'] = $field['settings'];
			unset($field['settings']);
		}
		$field['_version'] = $this->version;
		return $field;
	}

	/**
	 * Create new collection field settings instead of fieldsgroup
	 * Migrate data to new slug in DB
	 *
	 * @param array $field
	 * @param string $post_type
	 *
	 * @return array
	 */
	protected function updateFieldsgroup($field, $post_type)
	{
		// prepare new collection settings config
		$new_slug = '_' . ltrim($field['slug'], '_') . '_v3';

		$new_collection = array(
			'title' => $field['title'],
			'slug' => $new_slug,
			'enabled' => $field['enabled'],
			'_version' => $this->version,
			'_type' => 'collection',
			'fields' => array(),
		);

		$field['fields'] = trim($field['fields']);
		$fieldsgroup_fields = explode("\n", $field['fields']);

		// insert inputtext into collection
		if ( !empty($fieldsgroup_fields) ) {
			$is_group_title = 1;
			foreach ( $fieldsgroup_fields as $line ) {
				// parse settings
				$line = trim($line);
				$params = explode('|', $line, 2);
				if ( empty($params[1]) ) {
					$params[1] = $params[0];
				}

				$new_collection['fields'][ 'inputtext-' . $this->unique++ ] = array(
					'title' => $params[1],
					'description' => '',
					'slug' => $params[0],
					'enabled' => 1,
					'_type' => 'inputtext',
					'_version' => $this->version,
					'field_width' => '100',
					'group_title' => $is_group_title,
				);

				$is_group_title = 0;
			}
		}

		// migrate DB data to new slug
		if ( ! $this->isTestMode() ) {
			$this->importPostmeta($post_type, $field['slug'], $new_slug, array($this, 'formatFieldsgroupMeta'));
		}

		return $new_collection;
	}

	/**
	 * Create new collection field settings instead of fieldsgroup
	 * Migrate data to new slug in DB
	 *
	 * @param array $field
	 * @param string $post_type
	 *
	 * @return array
	 */
	protected function updateUploadmedia($field, $post_type)
	{
		// prepare new collection settings config
		$new_slug = '_' . ltrim($field['slug'], '_') . '_v3';

		$new_collection = array(
			'title' => $field['title'],
			'slug' => $new_slug,
			'enabled' => $field['enabled'],
			'_version' => $this->version,
			'_type' => 'collection',
			'fields' => array(),
		);

		// add simple media into collection
		$new_collection['fields'][ 'simplemedia-' . $this->unique++ ] = array(
			'title' => ($field['type'] == 'image')? 'Image' : 'File',
			'type' => $field['type'],
			'description' => '',
			'slug' => 'attachment_id',
			'enabled' => 1,
			'_type' => 'simplemedia',
			'_version' => $this->version,
			'field_width' => '25',
			//'group_title' => 0,
		);

		// title field
		if ( !empty($field['alt_title']) ) {
			$new_collection['fields'][ 'inputtext-' . $this->unique++ ] = array(
				'title' => 'Title',
				'description' => '',
				'slug' => 'title',
				'enabled' => 1,
				'_type' => 'inputtext',
				'_version' => $this->version,
				'field_width' => '75',
				'group_title' => 1,
			);
		}

		// description field
		if ( !empty($field['alt_descr']) ) {
			$new_collection['fields'][ 'textarea-' . $this->unique++ ] = array(
				'title' => 'Description',
				'editor' => 0,
				'description' => '',
				'slug' => 'description',
				'enabled' => 1,
				'_type' => 'textarea',
				'_version' => $this->version,
				'field_width' => '100',
				//'group_title' => 0,
			);
		}

		// migrate DB data to new slug
		if ( ! $this->isTestMode() ) {
			$this->importPostmeta($post_type, $field['slug'], $new_slug, array($this, 'formatUploadmediaMeta'));
		}

		return $new_collection;
	}

	/**
	 * Format fieldsgroup postmeta before save.
	 * Actually no changes required but we need to unserialize before writing to DB
	 *
	 * @param \stdClass $postmeta
	 *
	 * @return mixed
	 */
	protected function formatFieldsgroupMeta($postmeta)
	{
		return maybe_unserialize($postmeta->meta_value);
	}

	/**
	 * Format fieldsgroup postmeta before save.
	 * Actually no changes required but we need to unserialize before writing to DB
	 *
	 * @param \stdClass $postmeta
	 *
	 * @return mixed
	 */
	protected function formatUploadmediaMeta($postmeta)
	{
		$values = maybe_unserialize($postmeta->meta_value);
		if (empty($values)) return array();

		global $wpdb;
		$new_values = array();

		foreach ($values as $img_data) {
			$filename = $img_data['image'];
			$title = @$img_data['title'];
			$descr = @$img_data['description'];

			// search image in media to get an ID
			$attachment_id = $wpdb->get_var(
				$wpdb->prepare("
					SELECT ID 
					FROM $wpdb->posts 
					WHERE post_type = 'attachment'
					    AND post_status = 'inherit'
						AND guid = %s
				", $filename)
			);

			$new_values[] = array(
				'attachment_id' => $attachment_id,
				'title' => $title,
				'description' => $descr,
			);
		}

		return $new_values;
	}

}


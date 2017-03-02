<?php
/**
 * View for the Settings page
 */

use  WC_Discogs\Admin\Settings;

$discogs_db = new \WC_Discogs\API\Discogs\Database();
$results = $discogs_db->search();

print_r($results);

?>

<div class="wrap">
  Settings
  <table>
	  <tr>
		  <td>Consumer Key:</td>
		  <td><?php echo Settings::$options['discogs_api_consumer_key']; ?></td>
	  </tr>
	  <tr>
		  <td>Consumer Secret:</td>
		  <td><?php echo Settings::$options['discogs_api_consumer_secret']; ?></td>
	  </tr>
  </table>
</div>

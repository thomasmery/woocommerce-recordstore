<?php
/**
 * View for the Settings page
 */

 use  WC_Discogs\Admin\Settings;

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

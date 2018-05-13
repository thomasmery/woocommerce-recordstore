<p><?php echo __( 'Click here for an attempt at getting infos for this Release.', 'wc-recordstore' ); ?></p>
<div id="fetch-release-infos-action">
	<p>
		<label style="vertical-align: top" for="fetch-release-infos-action-search-title">Search Title</label>
		<textarea name="fetch-release-infos-action-search-title" placeholder="<?php echo $post_title; ?>"></textarea><br/>
		<em style="font-size: 0.9em; font-style: italic; margin-top: 4px"><?php echo __( 'You can try & modify the Search Title to get the expected results.', 'wc-recordstore' ); ?></em>
	</p>
	<p>
		<label style="vertical-align: top" for="fetch-release-infos-action-discogs-id">Discogs Code</label>
		<input type="text" name="fetch-release-infos-action-discogs-id"><br/>
		<em style="font-size: 0.9em; font-style: italic; margin-top: 4px"><?php echo __( 'You can use the Discogs Release Code if you  know it and get results for the exact release you\'re looking for.', 'wc-recordstore' ); ?></em>
	</p>
	<div style="margin-top: 10px">
		<input type="checkbox" name="fetch-release-infos-action-skip-master-release-search" value="1" />&nbsp;<label for="fetch-release-infos-action-skip-master-release-search">Skip Discogs Master Release search</label>
		<p style="font-size: 0.9em; font-style: italic; margin-top: 4px"><?php echo __( 'Check this box if you want to try to skip getting infos from a Discogs Master Release and try a Discogs Release directly (This can sometimes help when not getting the right infos (not when getting nothing at all).', 'wc-recordstore' ); ?></p>
	</div>
	<input type="submit" name="fetch-release-infos-action" class="submitfetch-release-infos fetch-release-infos button button-primary button-large" value="<?php echo __( 'Fetch Release Infos', 'wc-recordstore' ) ?>" />
</div>

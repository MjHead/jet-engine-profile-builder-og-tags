<cx-vui-tabs-panel
	name="og_tags_settings"
	label="OG Tags"
	key="og_tags_settings"
>
	<cx-vui-textarea
		name="og_tags_list"
		label="OG Tags list"
		description="Setup OG tags in next format: og:title=user.display_name. Put one tag per line"
		rows="10"
		:wrapper-css="[ 'equalwidth' ]"
		size="fullwidth"
		v-model="settings.og_tags_list"
	></cx-vui-textarea>
	<div
		class="cx-vui-component"
	>
		<div class="cx-vui-component__meta">
			<label class="cx-vui-component__label"><?php
				_e( 'Available tags:', 'jet-engine' );
			?></label>
			<div class="cx-vui-component__desc">
				og:title, og:url, og:image, og:description, og:locale<br>
				<a href="https://ogp.me/" target="_blank">More info</a>
			</div>
		</div>
	</div>
	<div
		class="cx-vui-component"
	>
		<div class="cx-vui-component__meta">
			<label class="cx-vui-component__label"><?php
				_e( 'Available values:', 'jet-engine' );
			?></label>
			<div class="cx-vui-component__desc">
				user.avatar, user.user_login, user.user_nicename, user.user_email, user.user_url, user.user_registered, user.display_name, user_field.first_name, user_field.last_name, user_field.description, user_field.custom_field - replace custom_field with any field you want
			</div>
		</div>
	</div>
	<cx-vui-component-wrapper
		:wrapper-css="[ 'vertical-fullwidth' ]"
	>
		<cx-vui-button
			button-style="accent"
			:loading="saving"
			@click="saveSettings"
		>
			<span
				slot="label"
				v-html="'<?php _e( 'Save', 'jet-engine' ); ?>'"
			></span>
		</cx-vui-button>
	</cx-vui-component-wrapper>
</cx-vui-tabs-panel>
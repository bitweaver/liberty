{strip}
<div class="display liberty">
	<div class="header">
		<h1>{tr}Flashvideo Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Flashvideo specific settings"}
			<p class="formhelp">
				You can find some information relating to this plugin on the <a class="external" href="http://www.bitweaver.org/wiki/TreasuryFlvPlugin">TreasuryFlvPlugin plugin page</a> at bitweaver.org.
			</p>

			{if $ffmpeg_extension}
				<p class="success">
					{biticon iname="dialog-ok" iexplain="OK"} {tr}The <a href="http://ffmpeg-php.sourceforge.net/">ffmpeg-php</a> extension is available.{/tr}
				</p>
			{else}
				<p class="warning">
					{biticon iname="dialog-warning" iexplain="Warining"} {tr}If possible, please install the <a href="http://ffmpeg-php.sourceforge.net/">ffmpeg-php</a> php extension. This plugin will work without the extension but many features will not work well such as video recognition and mp4 uploads.{/tr}
				</p>
			{/if}

			{if !$gLibertySystem->isPluginActive( 'mimeflv' )}
				{formfeedback error="This plugins has not been enabled. All settings you change here will have no effect on uploaded videos unless you enable the plugin in the liberty plugins administration screen"}
			{/if}

			{formfeedback hash=$feedback}
			<div class="row">
				{formlabel label="Path to ffmpeg" for="mime_flv_ffmpeg_path"}
				{forminput}
					<input type='text' name="mime_flv_ffmpeg_path" id="mime_flv_ffmpeg_path" size="40" value="{$gBitSystem->getConfig('mime_flv_ffmpeg_path')|escape|default:$ffmpeg_path}" />
					{formhelp note="If this path is not correct, please set the correct path to ffmpeg."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Video codec" for="mime_flv_video_codec"}
				{forminput}
					{html_options
						options=$rates.video_codec
						values=$rates.video_codec
						name=mime_flv_video_codec
						id=mime_flv_video_codec
						selected=$gBitSystem->getConfig('mime_flv_video_codec')|default:flv}
						{formhelp note="You can choose between codecs you wan to use to encode the uploaded video with. We recommend flashvideo if you don't require high quality videos.
						<dl>
							<dt>Flashvideo</dt><dd>Medium filesize, medium quality, fast encoding.</dd>
							<dt>MP4/AVC</dt><dd>Small filesize, high quality, slow encoding.</dd>
							<dt>MP4/AVC - 2 passes</dt><dd>Small filesize, very high quality, very slow encoding (this is likey to take at least as long as the video length).</dd>
						<dl>."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Force encode" for="mime_flv_force_encode"}
				{forminput}
					<input type='checkbox' name="mime_flv_force_encode" id="mime_flv_force_encode" value="y" {if $gBitSystem->isFeatureActive('mime_flv_force_encode')}checked="checked"{/if} />
					{formhelp note="The inline player supports videos encoded using the flv or h264 codec with mp3 audio. When users upload such videos, we can use those directly for streaming instead of re-encoding them. In some cases, the uploaded files might be excessively large for streaming and re-encoding takes care of that (requires ffmpeg-php)."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Video bitrate" for="mime_flv_video_bitrate"}
				{forminput}
					{html_options
						options=$rates.video_bitrate
						values=$rates.video_bitrate
						name=mime_flv_video_bitrate
						id=mime_flv_video_bitrate
						selected=$gBitSystem->getConfig('mime_flv_video_bitrate')|default:200000} kbits/s
					{formhelp note="Set the video bitrate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Video width" for="mime_flv_width"}
				{forminput}
					{html_options
						options=$rates.video_width
						values=$rates.video_width
						name=mime_flv_width
						id=mime_flv_width
						selected=$gBitSystem->getConfig('mime_flv_width')|default:320} pixel
					{formhelp note="Set the video width. We recommend 320 pixels. Height of the video will be adjusted automagically."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Audio sample rate" for="mime_flv_audio_samplerate"}
				{forminput}
					{html_options
						options=$rates.audio_samplerate
						values=$rates.audio_samplerate
						name=mime_flv_audio_samplerate
						id=mime_flv_audio_samplerate
						selected=$gBitSystem->getConfig('mime_flv_audio_samplerate')|default:22050} Hz
					{formhelp note="Set the audio sample rate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Audio bitrate" for="mime_flv_audio_bitrate"}
				{forminput}
					{html_options
						options=$rates.audio_bitrate
						values=$rates.audio_bitrate
						name=mime_flv_audio_bitrate
						id=mime_flv_audio_bitrate
						selected=$gBitSystem->getConfig('mime_flv_audio_bitrate')|default:32000} kbits/s
					{formhelp note="Set the audio bitrate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Default displayed size" for="mime_flv_default_size"}
				{forminput}
					{html_options
						options=$rates.display_size
						values=$rates.display_size
						name=mime_flv_default_size
						id=mime_flv_default_size
						selected=$gBitSystem->getConfig('mime_flv_default_size')}
					{formhelp note="If you are encoding small versions of the videos you can display larger versions. This will reduce video quality but make the encoded video smaller."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Foreground Colour" for="mime_flv_frontcolor"}
				{forminput}
					<input type='text' name="mime_flv_frontcolor" id="mime_flv_frontcolor" size="10" value="{$gBitSystem->getConfig('mime_flv_frontcolor')|default:"FFFFFF"}" />
					{formhelp note="Foreground colour of the progress bar."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Background Colour" for="mime_flv_backcolor"}
				{forminput}
					<input type='text' name="mime_flv_backcolor" id="mime_flv_backcolor" size="10" value="{$gBitSystem->getConfig('mime_flv_backcolor')|default:"000000"}" />
					{formhelp note="Background colour of the progress bar."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="plugin_settings" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}

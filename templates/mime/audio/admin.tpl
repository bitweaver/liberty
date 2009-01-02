{strip}
<div class="admin liberty">
	<div class="header">
		<h1>{tr}Flashvideo Plugin Settings{/tr}</h1>
	</div>

	<div class="body">
		{form legend="Flashvideo specific settings"}
			<p class="formhelp">
				You can find some information relating to this plugin on the <a class="external" href="http://www.bitweaver.org/wiki/LibertyMime+Flv+Plugin">LibertyMime Flv Plugin page</a> at bitweaver.org.
			</p>

			{if !$gLibertySystem->isPluginActive( 'mimeaudio' )}
				{formfeedback error="This plugins has not been enabled. All settings you change here will have no effect on uploaded videos unless you enable the plugin in the liberty plugins administration screen"}
			{/if}

			{formfeedback hash=$feedback}
			<p class="formhelp">{tr}You can spcify the path to either ffmpeg or mplayer and lame. If you have all applications installed, we will first try to convert audio files using ffmpeg and if that didn't work, we'll use mplayer and lame.{/tr}</p>

			<div class="row">
				{formlabel label="Path to ffmpeg" for="ffmpeg_path"}
				{forminput}
					<input type='text' name="ffmpeg_path" id="ffmpeg_path" size="40" value="{$gBitSystem->getConfig('ffmpeg_path')|escape|default:$ffmpeg_path}" />
					{formhelp note="If this path is not correct, please set the correct path to ffmpeg."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="MP3 Library" for="ffmpeg_mp3_lib"}
				{forminput}
					{html_options
						options=$options.mp3_lib
						values=$options.mp3_lib
						name=ffmpeg_mp3_lib
						id=ffmpeg_mp3_lib
						selected=$gBitSystem->getConfig('ffmpeg_mp3_lib')|default:libmp3lame}
						{formhelp note="MP3 library name when encoding audio stream. libmp3lame is used in recent versions of ffmpeg."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Path to mplayer" for="mplayer_path"}
				{forminput}
					<input type='text' name="mplayer_path" id="mplayer_path" size="40" value="{$gBitSystem->getConfig('mplayer_path')|escape|default:$mplayer_path}" />
					{formhelp note="If this path is not correct, please set the correct path to mplayer."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Path to lame" for="lame_path"}
				{forminput}
					<input type='text' name="lame_path" id="lame_path" size="40" value="{$gBitSystem->getConfig('lame_path')|escape|default:$lame_path}" />
					{formhelp note="If this path is not correct, please set the correct path to lame."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Lame options" for="mime_audio_lame_options"}
				{forminput}
					<input type='text' name="mime_audio_lame_options" id="mime_audio_lame_options" size="40" value="{$gBitSystem->getConfig('mime_audio_lame_options')|escape|default:$lame_options}" />
					{formhelp note="If you know your way around lame, you can insert your own options here to override the default settings."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Audio sample rate" for="mime_audio_samplerate"}
				{forminput}
					{html_options
						options=$options.audio_samplerate
						values=$options.audio_samplerate
						name=mime_audio_samplerate
						id=mime_audio_samplerate
						selected=$gBitSystem->getConfig('mime_audio_samplerate')|default:22050} Hz
					{formhelp note="Set the audio sample rate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Audio bitrate" for="mime_audio_bitrate"}
				{forminput}
					{html_options
						options=$options.audio_bitrate
						values=$options.audio_bitrate
						name=mime_audio_bitrate
						id=mime_audio_bitrate
						selected=$gBitSystem->getConfig('mime_audio_bitrate')|default:96000} kbits/s
					{formhelp note="Set the audio bitrate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Force encode" for="mime_audio_force_encode"}
				{forminput}
					<input type='checkbox' name="mime_audio_force_encode" id="mime_audio_force_encode" value="y" {if $gBitSystem->isFeatureActive('mime_audio_force_encode')}checked="checked"{/if} />
					{formhelp note="When mp3 files are uploaded they can be used directly for streaming. If you enable this, the uploaded mp3 will be re-encoded usually reducing filesize for streaming. The originally uploaded file will still be available for download."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Foreground Colour" for="mime_audio_frontcolor"}
				{forminput}
					<input type='text' name="mime_audio_frontcolor" id="mime_audio_frontcolor" size="10" value="{$gBitSystem->getConfig('mime_audio_frontcolor')|default:"FFFFFF"}" />
					{formhelp note="Foreground colour of the progress bar."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Background Colour" for="mime_audio_backcolor"}
				{forminput}
					<input type='text' name="mime_audio_backcolor" id="mime_audio_backcolor" size="10" value="{$gBitSystem->getConfig('mime_audio_backcolor')|default:"000000"}" />
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

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

			{if $ffmpeg_extension}
				<p class="success">
					{biticon iname="dialog-ok" iexplain="OK"} {tr}The <a class="external" href="http://ffmpeg-php.sourceforge.net/">ffmpeg-php</a> extension is available.{/tr}
				</p>
			{else}
				<p class="warning">
					{biticon iname="dialog-warning" iexplain="Warning"} {tr}If possible, please install the <a class="external" href="http://ffmpeg-php.sourceforge.net/">ffmpeg-php</a> php extension. This plugin will work without the extension but many features will not work well such as video recognition and mp4 uploads.{/tr}
				</p>
			{/if}

			{if !$gLibertySystem->isPluginActive( 'mimevideo' )}
				{formfeedback error="This plugins has not been enabled. All settings you change here will have no effect on uploaded videos unless you enable the plugin in the liberty plugins administration screen"}
			{/if}

			{formfeedback hash=$feedback}
			<div class="row">
				{formlabel label="Path to ffmpeg" for="ffmpeg_path"}
				{forminput}
					<input type='text' name="ffmpeg_path" id="ffmpeg_path" size="40" value="{$gBitSystem->getConfig('ffmpeg_path')|escape|default:$ffmpeg_path}" />
					{formhelp note="If this path is not correct, please set the correct path to ffmpeg."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Video codec" for="mime_video_video_codec"}
				{forminput}
					{html_options
						options=$options.video_codec
						values=$options.video_codec
						name=mime_video_video_codec
						id=mime_video_video_codec
						selected=$gBitSystem->getConfig('mime_video_video_codec')|default:flv}
						{formhelp note="You can choose between codecs you wan to use to encode the uploaded video with. We recommend flashvideo if you don't require high quality videos.
						<dl>
							<dt>Flashvideo</dt><dd>Medium filesize, medium quality, fast encoding.</dd>
							<dt>MP4/AVC</dt><dd>Small filesize, high quality, slow encoding.</dd>
							<dt>MP4/AVC - 2 passes</dt><dd>Small filesize, very high quality, very slow encoding (this is likey to take at least as long as the video length).</dd>
						</dl>"}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Force encode" for="mime_video_force_encode"}
				{forminput}
					<input type='checkbox' name="mime_video_force_encode" id="mime_video_force_encode" value="y" {if $gBitSystem->isFeatureActive('mime_video_force_encode')}checked="checked"{/if} />
					{formhelp note="The inline player supports videos encoded using the flv or h264 codec with mp3 audio. When users upload such videos, we can use those directly for streaming instead of re-encoding them. In some cases, the uploaded files might be excessively large for streaming and re-encoding takes care of that (requires ffmpeg-php)."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Path to MP4Box" for="mp4box_path"}
				{forminput}
					<input type='text' name="mp4box_path" id="mp4box_path" size="40" value="{$gBitSystem->getConfig('mp4box_path')|escape|default:$mp4box_path}" />
					{formhelp note="This is only necessary, when you upload MP4 videos and don't force that the video is re-encoded. Some video editing software such as <a href='http://fixounet.free.fr/avidemux' class='external'>avidemux</a>, <a href='http://www.adobe.com/' class='external'>Adobe Creative Suite 3 tools</a> (Premiere and After Effects) place the <em>MOOV atom</em> at the end of the MP4 file. This makes it impossible to stream the video and the user has to download the entire file before it can be played. If you have <strong>gpac</strong> installed, you can enter the path to <strong>MP4Box</strong> to reposition the <em>MOOV atom</em>."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Video bitrate" for="mime_video_video_bitrate"}
				{forminput}
					{html_options
						options=$options.video_bitrate
						values=$options.video_bitrate
						name=mime_video_video_bitrate
						id=mime_video_video_bitrate
						selected=$gBitSystem->getConfig('mime_video_video_bitrate')|default:200000} kbits/s
					{formhelp note="Set the video bitrate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Video width" for="mime_video_width"}
				{forminput}
					{html_options
						options=$options.video_width
						values=$options.video_width
						name=mime_video_width
						id=mime_video_width
						selected=$gBitSystem->getConfig('mime_video_width')|default:320} pixel
					{formhelp note="Set the video width. We recommend 320 pixels. Height of the video will be adjusted automagically."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Audio sample rate" for="mime_video_audio_samplerate"}
				{forminput}
					{html_options
						options=$options.audio_samplerate
						values=$options.audio_samplerate
						name=mime_video_audio_samplerate
						id=mime_video_audio_samplerate
						selected=$gBitSystem->getConfig('mime_video_audio_samplerate')|default:22050} Hz
					{formhelp note="Set the audio sample rate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Audio bitrate" for="mime_video_audio_bitrate"}
				{forminput}
					{html_options
						options=$options.audio_bitrate
						values=$options.audio_bitrate
						name=mime_video_audio_bitrate
						id=mime_video_audio_bitrate
						selected=$gBitSystem->getConfig('mime_video_audio_bitrate')|default:32000} kbits/s
					{formhelp note="Set the audio bitrate. The higher the bitrate the higher the quality but also the larger the file."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Default displayed size" for="mime_video_default_size"}
				{forminput}
					{html_options
						options=$options.display_size
						values=$options.display_size
						name=mime_video_default_size
						id=mime_video_default_size
						selected=$gBitSystem->getConfig('mime_video_default_size')}
					{formhelp note="If you are encoding small versions of the videos you can display larger versions. This will reduce video quality but make the encoded video smaller."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Foreground Colour" for="mime_video_frontcolor"}
				{forminput}
					<input type='text' name="mime_video_frontcolor" id="mime_video_frontcolor" size="10" value="{$gBitSystem->getConfig('mime_video_frontcolor')|default:"FFFFFF"}" />
					{formhelp note="Foreground colour of the progress bar."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Background Colour" for="mime_video_backcolor"}
				{forminput}
					<input type='text' name="mime_video_backcolor" id="mime_video_backcolor" size="10" value="{$gBitSystem->getConfig('mime_video_backcolor')|default:"000000"}" />
					{formhelp note="Background colour of the progress bar."}
				{/forminput}
			</div>

			<p class="warning">
				{biticon iname="dialog-warning" iexplain="Warning"} {tr}ffmpeg has a habit of changing the API when releasing new versions. Due to the demand for new features and the tendency to use cutting edge versions of ffmpeg it is very difficult to keep track of these API changes.{/tr}
			</p>

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
				{formlabel label="Motion estimation parameter" for="ffmpeg_me_method"}
				{forminput}
					{html_options
						options=$options.me_method
						values=$options.me_method
						name=ffmpeg_me_method
						id=ffmpeg_me_method
						selected=$gBitSystem->getConfig('ffmpeg_me_method')|default:me}
						{formhelp note="Motion estimeation parameter name. me_method is used in recent versions of ffmpeg."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="plugin_settings" value="{tr}Save Plugin Settings{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .liberty -->
{/strip}

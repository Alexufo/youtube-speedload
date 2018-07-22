=== Youtube SpeedLoad ===
Contributors: Alexufo
Tags: youtube, youtube thumbs,thumbnail, lazy load, optimizer, performance
Requires at least: 3.5
Tested up to:  4.9.7
Stable tag: 0.6.3
License: GPL2

Improve rendering speed pages with YouTube players.

== Description ==

This plugin improves your website page load speed substantially by replacing embedded YouTube videos, or YouTube links with a clickable preview image. The image is the original thumbnail from YouTube and features the play button, so users can click to play video and at that moment all necessary JavaScript will be loaded. It increases your website page load speed, especially on the sites with multiple YT video embeds

Github repo
https://github.com/Alexufo/youtube-speedload

Just click install and forget! 
== Installation ==
1.Upload the zip-file and unzip it in the /wp-content/plugins/ directory
2. Activate the plugin through the \'Plugins\' menu in WordPress

All YouTube links video like this will be affected:

http://youtube. com/watch?v=s3Mp7FY9oU0
http://www.youtube .com/watch?v=s3Mp7FY9oU0
http://youtu. be/s3Mp7FY9oU0 
http://www.youtube .com/watch?v=jCrT5zEW0IA 
http://www.youtube .com/playlist?list=PL6BCB25412319E08A

== Changelog ==
= 0.6.3 =
Optimize html
= 0.6.2 =
jQuery dependency removed
= 0.6.1 =
Move js in footer
= 0.6 =
Back to approach parse json instead html and remove microdata. Now hidden yt videos must parse good and faster.
= 0.5.1 =
major bug fix
= 0.5 =
bug fix
= 0.4.1 =
Add curl extension requirements and small fixes.
= 0.4 =
* Full rewrite. Now parse m.youtube page (16kb) instead json response from oembed url for microdata. 
* Add schema.org support for video.
* Add clear oembed cache in settings.
* Fixed quotes in video title

= 0.3 =
* Design update

= 0.2 =
* Add resposive option
* Add https support

= 0.1 =
* release


== How contact to Author? ==
alexufo@mail.ru i am online!

== Screenshots ==
1. before after: https://dl.dropboxusercontent.com/u/3013858/plugin/analysis.png
<?php
/**
 * Plugin Name: Youtube SpeedLoad
 * Plugin URI: http://serebniti.ru
 * Description: Friendly to WordPress.  Just click install and forget! Supports playlists and picks max thumbs from the server! Youtube SL replace standard wordpress embed code to thumbs from video what load youtube oembed code by click. Does not generate dependencies yourself. You can disable this plugin any time of use. You can do responsive embeds.
 * Version: 0.5
 * Text Domain: ytsl-textdomain
 * Domain Path: /lang
 * Author: Alexufo
 * Author URI: http://habrahabr.ru/users/alexufo/
 * License: GPL2
 */

add_action( 'plugins_loaded', 'ytsl_load_textdomain' );

function ytsl_load_textdomain() {
  load_plugin_textdomain( 'ytsl-textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/lang'  ); 
}

add_action( 'wp_enqueue_scripts', 'ytsl_plugin_styles_sripts' );
function ytsl_plugin_styles_sripts() {
	wp_enqueue_style( 'ytsl-textdomain', plugins_url('style.css', __FILE__) );
	wp_enqueue_script('ytsl-textdomain', plugins_url( 'script.js' , __FILE__ ), array( 'jquery' ));
}

if ( !is_admin() ) { 
	add_filter('embed_oembed_html', 'ytsl_oembed_html', 1, 3);
};




function ytsl_oembed_html ($cache, $url, $attr) {

	// ignor not youtube
	if (!strpos($cache, 'youtube')) {
		return $cache;
	};
	
	preg_match ( '/(?<=data-ytsl=")(.+?)(?=")/', $cache, $match_cache);
	
	$match_cache = $match_cache[0];
	

	//* if ytsl cache is empty we need create it ( video_id, title, picprefix and etc for schema.org ) for youtube videos and playlists
	if (empty($match_cache)) {
		
		// check curl
		if(!function_exists('curl_version')) {
			return $cache;
		}
		// remove old data attr older v0.3
		$cache   = preg_replace('/data-picprefix=\\"(.+?)\\"/s', "", $cache);
		// if playlist get id.  
		if( preg_match_all( '/videoseries|list=/i', $cache, $m )){
			// extract playlist id  
			preg_match( '/(?<=list=)(.+?)(?=")/', $cache, $list );
			//get video_id
			$json = json_decode(file_get_contents('http://www.youtube.com/oembed?url=http://www.youtube.com/playlist?list='.$list[1]), true);
			// $video_id extract
			preg_match( '/(?<=vi\/)(.+?)(?=\/)/', $json['thumbnail_url'], $video_id );
		} else {
			preg_match( '/(?<=embed\/)(.+?)(?=\?)/', $cache, $video_id );
		}
		//_log($video_id[0]);
		// if  video_id still is empty may be youtube offline :-)))
		
		if(!$video_id[0]) {
			return $cache;
		}

		$ch = curl_init();
		$headers = array(
			'Accept-language: en',
			'User-Agent: Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11B554a Safari',
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, "https://m.youtube.com/watch?hl=en&rel=0&client=mv-google&v=".$video_id[0]);

		$data = curl_exec($ch);
		$info = curl_getinfo($ch);
		//_log($info);
		curl_close($ch);
		
		if ($info['http_code'] != 200){
			return $cache;
		}

		$data = str_replace(array("\r","\n"),'', $data);		
		//_log($data);
		//json extraction
		preg_match("/var.bootstrap_data.=.\"\)\]\}\'(.*?)\<\/script/i", $data, $data);
		
		// if youtube change json
		if (empty($data)) {
			return $cache;
		}
		
		$data     = $data[1];
		$data_arr = str_split($data);
		$count 	  = '0';
		$index 	  = '0';
		
		// find end of json after var.bootstrap_data
		foreach ($data_arr as $el) {
			$index++;
			if($el == '{') {
				$count++;
			}
			if($el == '}') {
				$count--;
				if($count == '0') {
					$i = $index;
					break;
				}
			}
		}
		
		$json = stripslashes(substr($data, 0 , $i));

		//print_r($json);
		$json = json_decode($json,JSON_UNESCAPED_UNICODE);

		//print_r($json);
		$ytsl_cache  = array();
		$ytsl_cache['title'] = htmlentities( $json['content']['video']['title'], ENT_QUOTES, 'UTF-8' );
		
		$description = $json['content']['video_main_content']['contents']['0']['description']['runs']['0']['text'];
		$ytsl_cache['description'] = htmlentities( str_replace(array("\r","\n"),' ', $description), ENT_QUOTES, 'UTF-8' );
		
		$seconds = $json['content']['video']['length_seconds'];
		$ytsl_cache['duration']    = 'PT' . ($seconds/60)%60 . 'M'.$seconds%60 ."S";
		$ytsl_cache['video_id']    = $json['content']['video']['encrypted_id'];
		
		preg_match("/(..)default/im", $json['content']['video']['thumbnail_for_watch'], $picprefix);
		$ytsl_cache['picprefix']   = $picprefix[1];
		
		$published = $json['content']['video_main_content']['contents']['0']['date_text']['runs']['0']['text'];
		$ytsl_cache['published']   = date ( 'c', strtotime( str_replace('Published on ','', $published) ) );

		$ytsl_cache = base64_encode(json_encode($ytsl_cache));
		$cachekey   = '_oembed_' . md5( $url . serialize( $attr ) );
		// update $cache varable
		$cache      = str_replace('src', ' data-ytsl="'.$ytsl_cache.'"  src', $cache);
		// save new cache
		//_log($cache);
		update_post_meta( get_the_ID(), $cachekey, $cache );
		
		$match_cache = $ytsl_cache;
	}

	preg_match( '/(?<=height=")(.+?)(?=")/', $cache, $video_height );
	preg_match( '/(?<=width=")(.+?)(?=")/' , $cache, $video_width  );

	$json   = json_decode(base64_decode($match_cache), true);
	//_log($json);
	$ytsl   = preg_replace("/data-ytsl=\"(.+?)\"/", "", $cache);	
	$ytsl   = htmlentities(str_replace( '=oembed','=oembed&autoplay=1', $ytsl ));
	
	/**
	 * title
	 * published - uploadDate
	 * duration
	 * description
	 * video_id
	 * picprefix - SD or HD 
	 * thumb_url
	 * fixed - responsive or not
	 
	 **/

	$thumb_url  = "//img.youtube.com/vi/{$json['video_id']}/{$json['picprefix']}default.jpg";
	
	if(get_option('ytsl-responive') == 'on') {
		$wrap_start = '<div class="ytsl-wrapper">';
		$wrap_end   = '</div>';
		$fixed      = '';
	} else {
		$fixed      = "height:{$video_height[1]}px;width:{$video_width[1]}px;";
	}

	$html = "$wrap_start<div class=\"ytsl-click_div\" data-iframe=\"$ytsl\" style=\"$fixed position:relative;background: url('$thumb_url') no-repeat scroll center center / cover\" itemprop=\"video\" itemscope itemtype=\"http://schema.org/VideoObject\"><div class=\"ytsl-title_grad\"><div itemprop=\"name\" class=\"ytsl-title_text\">{$json['title']}</div></div><div itemprop=\"description\" style=\"display: none\" >{$json['description']}</div><img itemprop=\"thumbnailUrl\" src=\"http:$thumb_url\" style=\"display: none\" alt=\"{$json['title']}\"><meta itemprop=\"uploadDate\" content=\"{$json['published']}\" ><meta itemprop=\"duration\" content=\"{$json['duration']}\" ><link itemprop=\"contentUrl\" href=\"//www.youtube.com/watch?v={$json['video_id']}\" /><link itemprop=\"embedUrl\" href=\"//www.youtube.com/embed/{$json['video_id']}\" /><div class=\"ytsl-play_b\"></div></div>$wrap_end";

	return $html;
			
};

require  'ytsl-admin-options.php';



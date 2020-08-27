<?php

trait PlexHomepageItem
{
	public function testConnectionPlex()
	{
		if (!empty($this->config['plexURL']) && !empty($this->config['plexToken'])) {
			$url = $this->qualifyURL($this->config['plexURL']) . "/?X-Plex-Token=" . $this->config['plexToken'];
			try {
				$options = ($this->localURL($url)) ? array('verify' => false) : array();
				$response = Requests::get($url, array(), $options);
				libxml_use_internal_errors(true);
				if ($response->success) {
					$this->setAPIResponse('success', 'API Connection succeeded', 200);
					return true;
				}
			} catch (Requests_Exception $e) {
				$this->setAPIResponse('error', $e->getMessage(), 500);
				return false;
			};
		} else {
			$this->setAPIResponse('error', 'URL and/or Token not setup', 422);
			return 'URL and/or Token not setup';
		}
	}
	
	public function resolvePlexItem($item)
	{
		// Static Height & Width
		$height = $this->getCacheImageSize('h');
		$width = $this->getCacheImageSize('w');
		$nowPlayingHeight = $this->getCacheImageSize('nph');
		$nowPlayingWidth = $this->getCacheImageSize('npw');
		// Cache Directories
		$cacheDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
		$cacheDirectoryWeb = 'plugins/images/cache/';
		// Types
		switch ($item['type']) {
			case 'show':
				$plexItem['type'] = 'tv';
				$plexItem['title'] = (string)$item['title'];
				$plexItem['secondaryTitle'] = (string)$item['year'];
				$plexItem['summary'] = (string)$item['summary'];
				$plexItem['ratingKey'] = (string)$item['ratingKey'];
				$plexItem['thumb'] = (string)$item['thumb'];
				$plexItem['key'] = (string)$item['ratingKey'] . "-list";
				$plexItem['nowPlayingThumb'] = (string)$item['art'];
				$plexItem['nowPlayingKey'] = (string)$item['ratingKey'] . "-np";
				$plexItem['nowPlayingTitle'] = (string)$item['title'];
				$plexItem['nowPlayingBottom'] = (string)$item['year'];
				$plexItem['metadataKey'] = (string)$item['ratingKey'];
				break;
			case 'season':
				$plexItem['type'] = 'tv';
				$plexItem['title'] = (string)$item['parentTitle'];
				$plexItem['secondaryTitle'] = (string)$item['title'];
				$plexItem['summary'] = (string)$item['parentSummary'];
				$plexItem['ratingKey'] = (string)$item['parentRatingKey'];
				$plexItem['thumb'] = (string)$item['thumb'];
				$plexItem['key'] = (string)$item['ratingKey'] . "-list";
				$plexItem['nowPlayingThumb'] = (string)$item['art'];
				$plexItem['nowPlayingKey'] = (string)$item['ratingKey'] . "-np";
				$plexItem['metadataKey'] = (string)$item['parentRatingKey'];
				break;
			case 'episode':
				$plexItem['type'] = 'tv';
				$plexItem['title'] = (string)$item['grandparentTitle'];
				$plexItem['secondaryTitle'] = (string)$item['parentTitle'];
				$plexItem['summary'] = (string)$item['title'];
				$plexItem['ratingKey'] = (string)$item['parentRatingKey'];
				$plexItem['thumb'] = ($item['parentThumb'] ? (string)$item['parentThumb'] : (string)$item['grandparentThumb']);
				$plexItem['key'] = (string)$item['ratingKey'] . "-list";
				$plexItem['nowPlayingThumb'] = (string)$item['grandparentArt'];
				$plexItem['nowPlayingKey'] = (string)$item['grandparentRatingKey'] . "-np";
				$plexItem['nowPlayingTitle'] = (string)$item['grandparentTitle'] . ' - ' . (string)$item['title'];
				$plexItem['nowPlayingBottom'] = 'S' . (string)$item['parentIndex'] . ' · E' . (string)$item['index'];
				$plexItem['metadataKey'] = (string)$item['grandparentRatingKey'];
				break;
			case 'clip':
				$useImage = (isset($item['live']) ? "plugins/images/cache/livetv.png" : null);
				$plexItem['type'] = 'clip';
				$plexItem['title'] = (isset($item['live']) ? 'Live TV' : (string)$item['title']);
				$plexItem['secondaryTitle'] = '';
				$plexItem['summary'] = (string)$item['summary'];
				$plexItem['ratingKey'] = (string)$item['parentRatingKey'];
				$plexItem['thumb'] = (string)$item['thumb'];
				$plexItem['key'] = (string)$item['ratingKey'] . "-list";
				$plexItem['nowPlayingThumb'] = (string)$item['art'];
				$plexItem['nowPlayingKey'] = isset($item['ratingKey']) ? (string)$item['ratingKey'] . "-np" : (isset($item['live']) ? "livetv.png" : ":)");
				$plexItem['nowPlayingTitle'] = $plexItem['title'];
				$plexItem['nowPlayingBottom'] = isset($item['extraType']) ? "Trailer" : (isset($item['live']) ? "Live TV" : ":)");
				break;
			case 'album':
			case 'track':
				$plexItem['type'] = 'music';
				$plexItem['title'] = (string)$item['parentTitle'];
				$plexItem['secondaryTitle'] = (string)$item['title'];
				$plexItem['summary'] = (string)$item['title'];
				$plexItem['ratingKey'] = (string)$item['parentRatingKey'];
				$plexItem['thumb'] = (string)$item['thumb'];
				$plexItem['key'] = (string)$item['ratingKey'] . "-list";
				$plexItem['nowPlayingThumb'] = ($item['parentThumb']) ? (string)$item['parentThumb'] : (string)$item['art'];
				$plexItem['nowPlayingKey'] = (string)$item['parentRatingKey'] . "-np";
				$plexItem['nowPlayingTitle'] = (string)$item['grandparentTitle'] . ' - ' . (string)$item['title'];
				$plexItem['nowPlayingBottom'] = (string)$item['parentTitle'];
				$plexItem['metadataKey'] = isset($item['grandparentRatingKey']) ? (string)$item['grandparentRatingKey'] : (string)$item['parentRatingKey'];
				break;
			default:
				$plexItem['type'] = 'movie';
				$plexItem['title'] = (string)$item['title'];
				$plexItem['secondaryTitle'] = (string)$item['year'];
				$plexItem['summary'] = (string)$item['summary'];
				$plexItem['ratingKey'] = (string)$item['ratingKey'];
				$plexItem['thumb'] = (string)$item['thumb'];
				$plexItem['key'] = (string)$item['ratingKey'] . "-list";
				$plexItem['nowPlayingThumb'] = (string)$item['art'];
				$plexItem['nowPlayingKey'] = (string)$item['ratingKey'] . "-np";
				$plexItem['nowPlayingTitle'] = (string)$item['title'];
				$plexItem['nowPlayingBottom'] = (string)$item['year'];
				$plexItem['metadataKey'] = (string)$item['ratingKey'];
		}
		$plexItem['originalType'] = $item['type'];
		$plexItem['uid'] = (string)$item['ratingKey'];
		$plexItem['elapsed'] = isset($item['viewOffset']) && $item['viewOffset'] !== '0' ? (int)$item['viewOffset'] : null;
		$plexItem['duration'] = isset($item['duration']) ? (int)$item['duration'] : (int)$item->Media['duration'];
		$plexItem['addedAt'] = isset($item['addedAt']) ? (int)$item['addedAt'] : null;
		$plexItem['watched'] = ($plexItem['elapsed'] && $plexItem['duration'] ? floor(($plexItem['elapsed'] / $plexItem['duration']) * 100) : 0);
		$plexItem['transcoded'] = isset($item->TranscodeSession['progress']) ? floor((int)$item->TranscodeSession['progress'] - $plexItem['watched']) : '';
		$plexItem['stream'] = isset($item->Media->Part->Stream['decision']) ? (string)$item->Media->Part->Stream['decision'] : '';
		$plexItem['id'] = str_replace('"', '', (string)$item->Player['machineIdentifier']);
		$plexItem['session'] = (string)$item->Session['id'];
		$plexItem['bandwidth'] = (string)$item->Session['bandwidth'];
		$plexItem['bandwidthType'] = (string)$item->Session['location'];
		$plexItem['sessionType'] = isset($item->TranscodeSession['progress']) ? 'Transcoding' : 'Direct Playing';
		$plexItem['state'] = (((string)$item->Player['state'] == "paused") ? "pause" : "play");
		$plexItem['user'] = ($this->config['homepageShowStreamNames'] && $this->qualifyRequest($this->config['homepageShowStreamNamesAuth'])) ? (string)$item->User['title'] : "";
		$plexItem['userThumb'] = ($this->config['homepageShowStreamNames'] && $this->qualifyRequest($this->config['homepageShowStreamNamesAuth'])) ? (string)$item->User['thumb'] : "";
		$plexItem['userAddress'] = ($this->config['homepageShowStreamNames'] && $this->qualifyRequest($this->config['homepageShowStreamNamesAuth'])) ? (string)$item->Player['address'] : "x.x.x.x";
		$plexItem['address'] = $this->config['plexTabURL'] ? $this->config['plexTabURL'] . "/web/index.html#!/server/" . $this->config['plexID'] . "/details?key=/library/metadata/" . $item['ratingKey'] : "https://app.plex.tv/web/app#!/server/" . $this->config['plexID'] . "/details?key=/library/metadata/" . $item['ratingKey'];
		$plexItem['nowPlayingOriginalImage'] = 'api/v2/homepage/image?source=plex&img=' . $plexItem['nowPlayingThumb'] . '&height=' . $nowPlayingHeight . '&width=' . $nowPlayingWidth . '&key=' . $plexItem['nowPlayingKey'] . '$' . $this->randString();
		$plexItem['originalImage'] = 'api/v2/homepage/image?source=plex&img=' . $plexItem['thumb'] . '&height=' . $height . '&width=' . $width . '&key=' . $plexItem['key'] . '$' . $this->randString();
		$plexItem['openTab'] = $this->config['plexTabURL'] && $this->config['plexTabName'] ? true : false;
		$plexItem['tabName'] = $this->config['plexTabName'] ? $this->config['plexTabName'] : '';
		// Stream info
		$plexItem['userStream'] = array(
			'platform' => (string)$item->Player['platform'],
			'product' => (string)$item->Player['product'],
			'device' => (string)$item->Player['device'],
			'stream' => isset($item->Media) ? (string)$item->Media->Part['decision'] . ($item->TranscodeSession['throttled'] == '1' ? ' (Throttled)' : '') : '',
			'videoResolution' => (string)$item->Media['videoResolution'],
			'throttled' => ($item->TranscodeSession['throttled'] == 1) ? true : false,
			'sourceVideoCodec' => (string)$item->TranscodeSession['sourceVideoCodec'],
			'videoCodec' => (string)$item->TranscodeSession['videoCodec'],
			'audioCodec' => (string)$item->TranscodeSession['audioCodec'],
			'sourceAudioCodec' => (string)$item->TranscodeSession['sourceAudioCodec'],
			'videoDecision' => $this->streamType((string)$item->TranscodeSession['videoDecision']),
			'audioDecision' => $this->streamType((string)$item->TranscodeSession['audioDecision']),
			'container' => (string)$item->TranscodeSession['container'],
			'audioChannels' => (string)$item->TranscodeSession['audioChannels']
		);
		// Genre catch all
		if ($item->Genre) {
			$genres = array();
			foreach ($item->Genre as $key => $value) {
				$genres[] = (string)$value['tag'];
			}
		}
		// Actor catch all
		if ($item->Role) {
			$actors = array();
			foreach ($item->Role as $key => $value) {
				if ($value['thumb']) {
					$actors[] = array(
						'name' => (string)$value['tag'],
						'role' => (string)$value['role'],
						'thumb' => (string)$value['thumb']
					);
				}
			}
		}
		// Metadata information
		$plexItem['metadata'] = array(
			'guid' => (string)$item['guid'],
			'summary' => (string)$item['summary'],
			'rating' => (string)$item['rating'],
			'duration' => (string)$item['duration'],
			'originallyAvailableAt' => (string)$item['originallyAvailableAt'],
			'year' => (string)$item['year'],
			'studio' => (string)$item['studio'],
			'tagline' => (string)$item['tagline'],
			'genres' => ($item->Genre) ? $genres : '',
			'actors' => ($item->Role) ? $actors : ''
		);
		if (file_exists($cacheDirectory . $plexItem['nowPlayingKey'] . '.jpg')) {
			$plexItem['nowPlayingImageURL'] = $cacheDirectoryWeb . $plexItem['nowPlayingKey'] . '.jpg';
		}
		if (file_exists($cacheDirectory . $plexItem['key'] . '.jpg')) {
			$plexItem['imageURL'] = $cacheDirectoryWeb . $plexItem['key'] . '.jpg';
		}
		if (file_exists($cacheDirectory . $plexItem['nowPlayingKey'] . '.jpg') && (time() - 604800) > filemtime($cacheDirectory . $plexItem['nowPlayingKey'] . '.jpg') || !file_exists($cacheDirectory . $plexItem['nowPlayingKey'] . '.jpg')) {
			$plexItem['nowPlayingImageURL'] = 'api/v2/homepage/image?source=plex&img=' . $plexItem['nowPlayingThumb'] . '&height=' . $nowPlayingHeight . '&width=' . $nowPlayingWidth . '&key=' . $plexItem['nowPlayingKey'] . '';
		}
		if (file_exists($cacheDirectory . $plexItem['key'] . '.jpg') && (time() - 604800) > filemtime($cacheDirectory . $plexItem['key'] . '.jpg') || !file_exists($cacheDirectory . $plexItem['key'] . '.jpg')) {
			$plexItem['imageURL'] = 'api/v2/homepage/image?source=plex&img=' . $plexItem['thumb'] . '&height=' . $height . '&width=' . $width . '&key=' . $plexItem['key'] . '';
		}
		if (!$plexItem['nowPlayingThumb']) {
			$plexItem['nowPlayingOriginalImage'] = $plexItem['nowPlayingImageURL'] = "plugins/images/cache/no-np.png";
			$plexItem['nowPlayingKey'] = "no-np";
		}
		if (!$plexItem['thumb'] || $plexItem['addedAt'] >= (time() - 300)) {
			$plexItem['originalImage'] = $plexItem['imageURL'] = "plugins/images/cache/no-list.png";
			$plexItem['key'] = "no-list";
		}
		if (isset($useImage)) {
			$plexItem['useImage'] = $useImage;
		}
		return $plexItem;
	}
	
	public function getPlexHomepageStreams()
	{
		if (!$this->config['homepagePlexEnabled']) {
			$this->setAPIResponse('error', 'Plex homepage item is not enabled', 409);
			return false;
		}
		if (!$this->config['homepagePlexStreams']) {
			$this->setAPIResponse('error', 'Plex homepage module is not enabled', 409);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage item', 401);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexStreamsAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage module', 401);
			return false;
		}
		if (empty($this->config['plexURL'])) {
			$this->setAPIResponse('error', 'Plex URL is not defined', 422);
			return false;
		}
		if (empty($this->config['plexToken'])) {
			$this->setAPIResponse('error', 'Plex Token is not defined', 422);
			return false;
		}
		if (empty($this->config['plexID'])) {
			$this->setAPIResponse('error', 'Plex Id is not defined', 422);
			return false;
		}
		$ignore = array();
		$resolve = true;
		$url = $this->qualifyURL($this->config['plexURL']);
		$url = $url . "/status/sessions?X-Plex-Token=" . $this->config['plexToken'];
		$options = ($this->localURL($url)) ? array('verify' => false) : array();
		$response = Requests::get($url, array(), $options);
		libxml_use_internal_errors(true);
		if ($response->success) {
			$items = array();
			$plex = simplexml_load_string($response->body);
			foreach ($plex as $child) {
				if (!in_array($child['type'], $ignore) && isset($child['librarySectionID'])) {
					$items[] = $this->resolvePlexItem($child);
				}
			}
			$api['content'] = ($resolve) ? $items : $plex;
			$api['plexID'] = $this->config['plexID'];
			$api['showNames'] = true;
			$api['group'] = '1';
			$this->setAPIResponse('success', null, 200, $api);
			return $api;
		}
	}
	
	public function getPlexHomepageRecent()
	{
		if (!$this->config['homepagePlexEnabled']) {
			$this->setAPIResponse('error', 'Plex homepage item is not enabled', 409);
			return false;
		}
		if (!$this->config['homepagePlexRecent']) {
			$this->setAPIResponse('error', 'Plex homepage module is not enabled', 409);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage item', 401);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexRecentAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage module', 401);
			return false;
		}
		if (empty($this->config['plexURL'])) {
			$this->setAPIResponse('error', 'Plex URL is not defined', 422);
			return false;
		}
		if (empty($this->config['plexToken'])) {
			$this->setAPIResponse('error', 'Plex Token is not defined', 422);
			return false;
		}
		if (empty($this->config['plexID'])) {
			$this->setAPIResponse('error', 'Plex Id is not defined', 422);
			return false;
		}
		$ignore = array();
		$resolve = true;
		$url = $this->qualifyURL($this->config['plexURL']);
		$urls['movie'] = $url . "/hubs/home/recentlyAdded?X-Plex-Token=" . $this->config['plexToken'] . "&X-Plex-Container-Start=0&X-Plex-Container-Size=" . $this->config['homepageRecentLimit'] . "&type=1";
		$urls['tv'] = $url . "/hubs/home/recentlyAdded?X-Plex-Token=" . $this->config['plexToken'] . "&X-Plex-Container-Start=0&X-Plex-Container-Size=" . $this->config['homepageRecentLimit'] . "&type=2";
		$urls['music'] = $url . "/hubs/home/recentlyAdded?X-Plex-Token=" . $this->config['plexToken'] . "&X-Plex-Container-Start=0&X-Plex-Container-Size=" . $this->config['homepageRecentLimit'] . "&type=8";
		foreach ($urls as $k => $v) {
			$options = ($this->localURL($v)) ? array('verify' => false) : array();
			$response = Requests::get($v, array(), $options);
			libxml_use_internal_errors(true);
			if ($response->success) {
				$items = array();
				$plex = simplexml_load_string($response->body);
				foreach ($plex as $child) {
					if (!in_array($child['type'], $ignore) && isset($child['librarySectionID'])) {
						$items[] = $this->resolvePlexItem($child);
					}
				}
				if (isset($api)) {
					$api['content'] = array_merge($api['content'], ($resolve) ? $items : $plex);
				} else {
					$api['content'] = ($resolve) ? $items : $plex;
				}
			}
		}
		if (isset($api['content'])) {
			usort($api['content'], function ($a, $b) {
				return $b['addedAt'] <=> $a['addedAt'];
			});
		}
		$api['plexID'] = $this->config['plexID'];
		$api['showNames'] = true;
		$api['group'] = '1';
		$this->setAPIResponse('success', null, 200, $api);
		return $api;
	}
	
	public function getPlexHomepageMetadata($array)
	{
		if (!$this->config['homepagePlexEnabled']) {
			$this->setAPIResponse('error', 'Plex homepage item is not enabled', 409);
			return false;
		}
		if (!$this->config['homepagePlexStreams']) {
			$this->setAPIResponse('error', 'Plex homepage module is not enabled', 409);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage item', 401);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexStreamsAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage module', 401);
			return false;
		}
		if (empty($this->config['plexURL'])) {
			$this->setAPIResponse('error', 'Plex URL is not defined', 422);
			return false;
		}
		if (empty($this->config['plexToken'])) {
			$this->setAPIResponse('error', 'Plex Token is not defined', 422);
			return false;
		}
		if (empty($this->config['plexID'])) {
			$this->setAPIResponse('error', 'Plex Id is not defined', 422);
			return false;
		}
		$key = $array['key'] ?? null;
		if (!$key) {
			$this->setAPIResponse('error', 'Plex Metadata key is not defined', 422);
			return false;
		}
		$ignore = array();
		$resolve = true;
		$url = $this->qualifyURL($this->config['plexURL']);
		$url = $url . "/library/metadata/" . $key . "?X-Plex-Token=" . $this->config['plexToken'];
		$options = ($this->localURL($url)) ? array('verify' => false) : array();
		$response = Requests::get($url, array(), $options);
		libxml_use_internal_errors(true);
		if ($response->success) {
			$items = array();
			$plex = simplexml_load_string($response->body);
			foreach ($plex as $child) {
				if (!in_array($child['type'], $ignore) && isset($child['librarySectionID'])) {
					$items[] = $this->resolvePlexItem($child);
				}
			}
			$api['content'] = ($resolve) ? $items : $plex;
			$api['plexID'] = $this->config['plexID'];
			$api['showNames'] = true;
			$api['group'] = '1';
			$this->setAPIResponse('success', null, 200, $api);
			return $api;
		}
	}
	
	public function getPlexHomepagePlaylists()
	{
		if (!$this->config['homepagePlexEnabled']) {
			$this->setAPIResponse('error', 'Plex homepage item is not enabled', 409);
			return false;
		}
		if (!$this->config['homepagePlexPlaylist']) {
			$this->setAPIResponse('error', 'Plex homepage module is not enabled', 409);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage item', 401);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexPlaylistAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage module', 401);
			return false;
		}
		if (empty($this->config['plexURL'])) {
			$this->setAPIResponse('error', 'Plex URL is not defined', 422);
			return false;
		}
		if (empty($this->config['plexToken'])) {
			$this->setAPIResponse('error', 'Plex Token is not defined', 422);
			return false;
		}
		if (empty($this->config['plexID'])) {
			$this->setAPIResponse('error', 'Plex Id is not defined', 422);
			return false;
		}
		$url = $this->qualifyURL($this->config['plexURL']);
		$url = $url . "/playlists?X-Plex-Token=" . $this->config['plexToken'];
		$options = ($this->localURL($url)) ? array('verify' => false) : array();
		$response = Requests::get($url, array(), $options);
		libxml_use_internal_errors(true);
		if ($response->success) {
			$items = array();
			$plex = simplexml_load_string($response->body);
			foreach ($plex as $child) {
				if ($child['playlistType'] == "video" && strpos(strtolower($child['title']), 'private') === false) {
					$playlistTitleClean = preg_replace("/(\W)+/", "", (string)$child['title']);
					$playlistURL = $this->qualifyURL($this->config['plexURL']);
					$playlistURL = $playlistURL . $child['key'] . "?X-Plex-Token=" . $this->config['plexToken'];
					$options = ($this->localURL($url)) ? array('verify' => false) : array();
					$playlistResponse = Requests::get($playlistURL, array(), $options);
					if ($playlistResponse->success) {
						$playlistResponse = simplexml_load_string($playlistResponse->body);
						$items[$playlistTitleClean]['title'] = (string)$child['title'];
						foreach ($playlistResponse->Video as $playlistItem) {
							$items[$playlistTitleClean][] = $this->resolvePlexItem($playlistItem);
						}
					}
				}
			}
			$api['content'] = $items;
			$api['plexID'] = $this->config['plexID'];
			$api['showNames'] = true;
			$api['group'] = '1';
			$this->setAPIResponse('success', null, 200, $api);
			return $api;
		} else {
			$this->setAPIResponse('error', 'Plex API error', 500);
			return false;
		}
		
	}
	
	public function getPlexHomepageSearch($query)
	{
		if (!$this->config['homepagePlexEnabled']) {
			$this->setAPIResponse('error', 'Plex homepage item is not enabled', 409);
			return false;
		}
		if (!$this->qualifyRequest($this->config['homepagePlexAuth'])) {
			$this->setAPIResponse('error', 'User not approved to view this homepage item', 401);
			return false;
		}
		if (empty($this->config['plexURL'])) {
			$this->setAPIResponse('error', 'Plex URL is not defined', 422);
			return false;
		}
		if (empty($this->config['plexToken'])) {
			$this->setAPIResponse('error', 'Plex Token is not defined', 422);
			return false;
		}
		if (empty($this->config['plexID'])) {
			$this->setAPIResponse('error', 'Plex Id is not defined', 422);
			return false;
		}
		$query = $query ?? null;
		if (!$query) {
			$this->setAPIResponse('error', 'Plex Metadata key is not defined', 422);
			return false;
		}
		$ignore = array('artist', 'episode');
		$resolve = true;
		$url = $this->qualifyURL($this->config['plexURL']);
		$url = $url . "/search?query=" . rawurlencode($query) . "&X-Plex-Token=" . $this->config['plexToken'];
		$options = ($this->localURL($url)) ? array('verify' => false) : array();
		$response = Requests::get($url, array(), $options);
		libxml_use_internal_errors(true);
		if ($response->success) {
			$items = array();
			$plex = simplexml_load_string($response->body);
			foreach ($plex as $child) {
				if (!in_array($child['type'], $ignore) && isset($child['librarySectionID'])) {
					$items[] = $this->resolvePlexItem($child);
				}
			}
			$api['content'] = ($resolve) ? $items : $plex;
			$api['plexID'] = $this->config['plexID'];
			$api['showNames'] = true;
			$api['group'] = '1';
			$this->setAPIResponse('success', null, 200, $api);
			return $api;
		}
	}
}
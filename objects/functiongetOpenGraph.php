<?php
echo PHP_EOL."<!-- OpenGraph -->".PHP_EOL;
if (empty($videos_id)) {
    echo PHP_EOL."<!-- OpenGraph no video id -->".PHP_EOL;
    if (!empty($_GET['videoName'])) {
        echo PHP_EOL."<!-- OpenGraph videoName {$_GET['videoName']} -->".PHP_EOL;
        $video = Video::getVideoFromCleanTitle($_GET['videoName']);
    }
} else {
    echo PHP_EOL."<!-- OpenGraph videos_id {$videos_id} -->".PHP_EOL;
    $video = Video::getVideoLight($videos_id);
}
if (empty($video)) {
    echo PHP_EOL."<!-- OpenGraph no video -->".PHP_EOL;
    return false;
}
$videos_id = $video['id'];
$source = Video::getSourceFile($video['filename']);
$imgw = 1024;
$imgh = 768;

$contentType = '<meta property="og:video:type" content="video/mp4" />';

$ogtype = 'video.other';
switch ($video['type']) {
    case "audio":
    case "linkAudio":
        $ogtype = 'music.song';
        $contentType = '<meta property="og:audio:type" content="audio/mpeg" />';

        break;
    case "pdf":
    case "article":
        $ogtype = 'article';
        $contentType = '<meta property="og:type" content="article" />';
        break;

    default:
        $ogtype = 'video.other';
        $type = Video::getVideoTypeText($video['filename']);
        if($type = 'HLS'){
            $contentType = '<meta property="og:video:type" content="application/x-mpegURL" />';
        }
        break;
}

if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
    $img = $source['url'];
    $data = getimgsize($source['path']);
    $imgw = $data[0];
    $imgh = $data[1];
} elseif ($video['type'] == "audio") {
    $img = ImagesPlaceHolders::getAudioLandscape(ImagesPlaceHolders::$RETURN_URL);
}
$type = 'video';
if ($video['type'] === 'pdf') {
    $type = 'pdf';
}
if ($video['type'] === 'article') {
    $type = 'article';
}
$images = Video::getImageFromFilename($video['filename'], $type);
if (!isImageNotFound($images->posterPortraitThumbs)) {
    $img = $images->posterPortraitThumbs;
    $imgw = 200;
    $imgh = 800;
} else if (!isImageNotFound($images->posterPortrait)) {
    $img = $images->posterPortrait;
    $data = getimgsize($images->posterPortraitPath);
    $imgw = $data[0];
    $imgh = $data[1];
} if (!isImageNotFound($images->posterLandscapeThumbs)) {
    $img = $images->posterLandscapeThumbs;
    $imgw = 500;
    $imgh = 280;
} else if (!isImageNotFound($images->posterLandscape)) {
    $img = $images->posterLandscape;
    $data = getimgsize($images->posterLandscapePath);
    $imgw = $data[0];
    $imgh = $data[1];
} else {
    $img = $images->poster;
}
//var_dump($img, $images);exit;
$twitter_site = $advancedCustom->twitter_site;
$title = getSEOTitle($video['title']);
$description = getSEODescription($video['description']);
//$ogURL = Video::getLink($video['id'], $video['clean_title']);
if(!empty($_REQUEST['playlists_id'])){
    $ogURL = PlayLists::getLink($_REQUEST['playlists_id'],isEmbed(), @$_REQUEST['playlist_index']);
}else if(!empty($_REQUEST['tags_id']) && isset($_REQUEST['playlist_index'])){
    $ogURL = PlayLists::getTagLink($_REQUEST['tags_id'],isEmbed(), @$_REQUEST['playlist_index']);
}else{
    $ogURL = Video::getLinkToVideo($videos_id, '', false,Video::$urlTypeCanonical, [], true);
}

$modifiedDate = date('Y-m-d', strtotime($video['modified']));
$createddDate = date('Y-m-d', strtotime($video['created']));
echo $contentType;
?>
<!-- og from <?php echo basename(__FILE__); ?> -->
<meta http-equiv="last-modified"       content="<?php echo $modifiedDate; ?>">
<meta name="revised"                   content="<?php echo $modifiedDate; ?>" />
<link rel="image_src"                  href="<?php echo $img; ?>" />
<meta property="og:image"              content="<?php echo $img; ?>" />
<meta property="og:image:secure_url"   content="<?php echo $img; ?>" />
<meta property="og:image:type"         content="image/jpeg" />
<meta property="og:image:width"        content="<?php echo $imgw; ?>" />
<meta property="og:image:height"       content="<?php echo $imgh; ?>" />
<meta property="fb:app_id"             content="774958212660408" />
<meta property="og:title"              content="<?php echo $title; ?>" />
<meta property="og:description"        content="<?php echo $description; ?>" />
<meta property="og:url"                content="<?php echo $ogURL; ?>" />
<meta property="og:type"               content="<?php echo $ogtype; ?>" />

<meta property="ya:ovs:upload_date"    content="<?php echo $createddDate; ?>" />
<meta property="ya:ovs:adult"          content="no" />
<meta property="video:duration"        content="<?php echo $video['duration_in_seconds']; ?>" />


<link rel="canonical" href="<?php echo $ogURL; ?>" />

<?php
$source = Video::getHigestResolution($video['filename']);
if (empty($source['url'])) {
    if (CustomizeUser::canDownloadVideos()) {
        echo "<!-- you cannot download videos we will not share the video source file -->";
    }
    if (empty($source['url'])) {
        echo "<!-- we could not get the MP4 source file -->";
    }
} else {
    $source['url'] = str_replace(".m3u8", ".m3u8.mp4", $source['url']);
}
if (!AVideoPlugin::isEnabledByName("SecureVideosDirectory") && !empty($source['url'])) {
    ?>
    <meta property="og:video"            content="<?php echo $source['url']; ?>" />
    <meta property="og:video:secure_url" content="<?php echo $source['url']; ?>" />
    <meta property="og:video:type"       content="video/mp4" />
    <meta property="og:video:width"      content="<?php echo $imgw; ?>" />
    <meta property="og:video:height"     content="<?php echo $imgh; ?>" />
    <?php
} else {
        if (AVideoPlugin::isEnabledByName("SecureVideosDirectory")) {
            echo "<!-- SecureVideosDirectory plugin is enabled we will not share the video source file -->";
        }
        if (empty($source['url'])) {
            echo "<!-- we could not get the source file -->";
        } ?>
    <meta property="og:video"            content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
    <meta property="og:video:secure_url" content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
    <?php
    }
?>
<meta property="duration"                content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />

<!-- Twitter cards -->
<?php
if (!empty($advancedCustom->twitter_player)) {
    if (!AVideoPlugin::isEnabledByName("SecureVideosDirectory") && !empty($source['url'])) {
        ?>
    <meta name="twitter:card" content="player" />
    <meta name="twitter:player" content="<?php echo Video::getLinkToVideo($videos_id, $video['clean_title'], true); ?>" />
    <meta name="twitter:player:width" content="<?php echo $imgw; ?>" />
    <meta name="twitter:player:height" content="<?php echo $imgh; ?>" />
    <meta name="twitter:player:stream" content="<?php echo $source['url']; ?>" />
    <meta name="twitter:player:stream:content_type" content="video/mp4" />
    <?php
    } else {
        ?>
    <meta name="twitter:card" content="player" />
    <meta name="twitter:player" content="<?php echo Video::getLinkToVideo($videos_id, $video['clean_title'], true); ?>" />
    <meta name="twitter:player:width" content="480" />
    <meta name="twitter:player:height" content="480" />
    <?php
    }
} else {
    if (!empty($advancedCustom->twitter_summary_large_image)) {
        ?>
        <meta name="twitter:card" content="summary_large_image" />
        <?php
    } else {
        ?>
        <meta name="twitter:card" content="summary" />
        <?php
    }
}
?>
<meta name="twitter:site" content="<?php echo $twitter_site; ?>" />
<meta name="twitter:url" content="<?php echo $ogURL; ?>"/>
<meta name="twitter:title" content="<?php echo $title; ?>"/>
<meta name="twitter:description" content="<?php echo $description; ?>"/>
<meta name="twitter:image" content="<?php echo $img; ?>"/>
<?php

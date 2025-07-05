<?php
$base = 'https://www.legislation.gov.uk/';
$url = $_GET['url'] ?? '';
$content = '';
$style = '';
if ($url) {
    $url = preg_replace('#^https:/+(www.)?legislation.gov.uk/#', '', $url);

    # Fetch main page for next/previous
    $main = file_get_contents($base . $url);
    preg_match('#<link rel="index" href="(.*?)" title="(.*?)"#', $main, $nav_index);
    preg_match('#<link rel="up" href="(.*?)" title="(.*?)"#', $main, $nav_up);
    preg_match('#<link rel="prev" href="(.*?)" title="(.*?)"#', $main, $nav_prev);
    preg_match('#<link rel="next" href="(.*?)" title="(.*?)"#', $main, $nav_next);

    # Fetch HTML5 output for simpler output
    $content = file_get_contents($base . $url . '/data.html');

    # Correct style file
    preg_match('#HTML5_styles/(.*?)\.css#', $content, $m);
    $style = $m[1];

    # Tidy
    $content = preg_replace('#^.*?<body>#s', '', $content); # Start
    $content = preg_replace('#</body>.*#s', '', $content); # End
    $content = preg_replace('#href="http://www.legislation.gov.uk#', 'href="', $content); # Internal links
    $content = preg_replace('#src="/#', 'src="' . $base, $content); # Images
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<title>Ad Legem – readable legislation.gov.uk on mobile</title>
<style>
body { font-family: system-ui; margin: 0; padding: 0; }
body > main { padding: 1em; }
.surround { background-color: #222222; color: white; padding: 0.5em; }
.surround h1 { margin: 0; }
.surround a { color: white; text-decoration: underline }
.surround h1 a { text-decoration: none }
.surround :first-child { margin-top: 0; }
.docTitle { font-size: 200%; }
form { display: flex; gap: 0.25em; }
form input { flex: 1; max-width: 30em; }
nav { margin: 0.5em; display: flex; gap: 0.5em; justify-content: center; }
nav > * { text-align: center; }
img { max-width: 100%; }
#intro-pics a { max-width: 320px; }
@media (min-width: 40em) {
    #intro { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: auto 1fr; gap: 1em; }
    #intro-text { grid-column: 1; grid-row: 1;}
    #intro-pics { grid-column: 2; grid-row: 1 / 3; }
    #intro-help { grid-column: 1; grid-row: 2; }
}
</style>
<?php if ($style) { ?><link href="/<?=$style ?>.css" rel="stylesheet"><?php } ?>
</head>
<body>
<header class="surround">
<h1><a href="/">adlegem.dracos.co.uk</a></h1>
<form>
<span>legislation.gov.uk URL:</span>
<input type="text" name="url" value="<?=htmlspecialchars($url) ?>">
</form>

</header>
<?php
if ($content) {
    print '<nav>';
    if ($nav_index) {
        print '<a href="' . $nav_index[1] . '">&bull;<br>' . $nav_index[2] . '</a>';
    }
    if ($nav_prev) {
        $nav_prev[2] = preg_replace('#^.*?; #', '', $nav_prev[2]);
        $nav_prev[2] = preg_replace('# ([0-9])#', '&nbsp;\1', $nav_prev[2]);
        print '<a href="' . $nav_prev[1] . '">&larr;<br>' . $nav_prev[2] . '</a>';
    }
    if ($nav_up) {
        print '<a href="' . $nav_up[1] . '">&uparrow;<br>' . $nav_up[2] . '</a>';
    }
    if ($nav_next) {
        $nav_next[2] = preg_replace('#^.*?; #', '', $nav_next[2]);
        $nav_next[2] = preg_replace('# ([0-9])#', '&nbsp;\1', $nav_next[2]);
        print '<a href="' . $nav_next[1] . '">&rarr;<br>' . $nav_next[2] . '</a>';
    }
    print '</nav>';
    print '<main>';
    print $content;
} else {
?>
<main id="intro">

<div id="intro-text">
<p>Followed a legislation.gov.uk link on your phone?
Found it impossible to read?
<p>This site will try and make it readable, and turn
<a href="https://www.legislation.gov.uk/ukpga/2024/25/section/1">any page on the official site</a>
into
<a href="https://adlegem.dracos.co.uk/ukpga/2024/25/section/1">a readable version of the same page</a>.
</p>

</div>

<div id="intro-pics">
<p style="margin-top:2em;display:flex;gap:0.5em;justify-content:space-evenly;align-items:center;">
<a href="https://www.legislation.gov.uk/ukpga/2024/25/section/1"><img src="official-small.png" alt="A mobile screenshot of the official legislation.gov.uk site, which does not work well on mobile and all the text is really tiny."></a>
<span>&rarr;</span>
<a href="https://adlegem.dracos.co.uk/ukpga/2024/25/section/1"><img src="mine-small.png" alt="A mobile screenshot of this site showing the same page, where the text is readable and links are easily clickable."></a>
</p>
</div>

<div id="intro-help">
<h2>How to use</h2>
<p>
On an <strong>iPhone</strong>, you can add <a href="https://www.icloud.com/shortcuts/a8e923b15b6445ab9d281223ca1a5ae7">this Apple Shortcut</a>
and then when you’re on a legislation.gov.uk page, click the Share button, and Share the page to this shortcut, and it
should then take you to the readable version of the same page.

<p>On <strong>Android</strong> or other mobiles, you should be able to copy and paste the URL into the box on this page, I don’t know if there’s an easier way, let me know.
</p>

<h2>Acknowledgements</h2>
<p>Thanks to <a href="https://www.legislation.gov.uk/">legislation.gov.uk</a> for their (apart from this aspect) very nice website and structure, and the huge amount of work that must go in to running it;
<a href="https://www.mythic-beasts.com/">Mythic Beasts</a> for hosting;
<a href="https://www.css-tricks.com/">CSS Tricks</a> for flexbox/grid help.
</p>
</div>

<?php
}
?>
</main>
<footer class="surround">
<?php
if ($url) {
    print '<p><a href="' . $base . htmlspecialchars($url) . '">Page on official site</a></p>';
}
?>

<p>Made by <a href="https://dracos.co.uk/">Matthew</a>

<p><small>© Crown and database right. Derived from content available under the <a href="https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/">Open Government Licence v3.0</a> from <a href="<?=$base ?>">legislation.gov.uk</a>.
Material derived from the European Institutions © European Union, 1998-2019 and re-used under the terms of the Commission Decision 2011/833/EU.
Westlaw UK derived from Crown Copyright material and contributed to legislation.gov.uk.
British History Online derived from Crown Copyright material and contributed to legislation.gov.uk.
</small></p>
</footer>
</body>
</html>

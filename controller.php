<h3>ผลลัพธ์: </h3>
<div style="padding: 10px; border: 4px solid blue;">
<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$vowels = [
    'a' => ['ก่ะ', 'กั', 'กั่'],
    'i' => ['กิ่', 'กิ', 'กิ่'],
    'u' => ['กุ่', 'กุ', 'กุ่'],
    'ue' => ['กึ่', 'กึ', 'กึ่'],
    'e' => ['เก่ะ', 'เก็', 'เก่'],
    "ae" => ['แก่ะ', 'แก็', 'แก่'],
    'o' => ['โก่ะ', 'ก', 'ก่'],
    'oa' => ['เก่าะ', 'ก็อ', 'ก่อ'],
    'oe' => ['เก่อะ', 'เกิ่', 'เกิ่'],
    'ia' => ['เกี่ยะ', 'เกีย', 'เกี่ย'],
    'ua' => ['กั่วะ', 'กว', 'ก่ว'],
    'uea' => ['เกื่อะ', 'เกื่อ', 'เกื่อ'],
    'au' => ['เก่า'],
    'ai' => ['ไก่'],
    'am' => ['ก่ำ'],
    'oej' => ['เก่ย'],

    'a:' => ['ก่า', 'กา', 'ก่า'],
    'i:' => ['กี่', 'กี', 'กี่'],
    'u:' => ['กู่', 'กู', 'กู่'],
    'ue:' => ['กื่อ', 'กื', 'กื่'],
    'e:' => ['เก่', 'เก', 'เก่'],
    'ae:' => ['แก่', 'แก', 'แก่'],
    'o:' => ['โก่', 'โก', 'โก่'],
    'oa:' => ['ก่อ', 'กอ', 'ก่อ'],
    'oe:' => ['เก่อ', 'เกิ', 'เกิ่'],
    'i:a' => ['เกี่ย', 'เกีย', 'เกี่ย'],
    'u:a' => ['กั่ว', 'กว', 'ก่ว'],
    'ue:a' => ['เกื่อ', 'เกือ', 'เกื่อ'],
];
$consonants = [
    'k' => ['ก'],
    'kh' => ['ข', 'ค'],
    'ng' => ['หง', 'ง'],
    'c' => ['จ'],
    'ch' => ['ฉ', 'ช'],
    'b' => ['บ'],
    'p' => ['ป'],
    'ph' => ['ผ', 'พ'],
    'f' => ['ฝ', 'ฟ'],
    'm' => ['หม', 'ม'],
    'd' => ['ด'],
    't' => ['ต'],
    'th' => ['ถ', 'ท'],
    'n' => ['หน', 'น'],
    'j' => ['หย', 'ย'],
    'r' => ['หร', 'ร'],
    'l' => ['หล', 'ล'],
    'w' => ['หว', 'ว'],
    's' => ['ส', 'ซ'],
    'h' => ['ห', 'ฮ'],
    'q' => ['อ'],
];
$finalConsonants = [
    'k' => 'ก',
    'p' => 'บ',
    't' => 'ด',
    'n' => 'น',
    'm' => 'ม',
    'j' => 'ย',
    'w' => 'ว',
    'ng' => 'ง',
    'q' => ''
];
$toneMarks = [
    'ก',
    'ก่',
    'ก้',
    'ก๊',
    'ก๋'
];

class Syl {
    public $initial = '';
    public $secondInitial = '';
    public $final = '';
    public $vowel = '';
    public $tone = 0;

    public function __construct ($syl_raw = null) {
        global $consonants, $vowels;
        if(empty($syl_raw)) return;
        for($i=0; $i < strlen($syl_raw); $i++) {
            if(array_key_exists($syl_raw[$i], $consonants) || $syl_raw[$i] == "g"){
                if(empty($this->vowel)) {
                    if(in_array($syl_raw[$i], ['r', 'l', 'w']) && !empty($this->initial)) {
                        $this->secondInitial = $syl_raw[$i];
                    } else {
                        $this->initial .= $syl_raw[$i];
                    }
                } else {
                    if($syl_raw[$i] !== 'q') $this->final .= $syl_raw[$i];
                }
            } else if(array_key_exists($syl_raw[$i], $vowels) || $syl_raw[$i] == ":") {
                $this->vowel .= $syl_raw[$i];
            } else if(is_numeric($syl_raw[$i])) {
                $this->tone = intval($syl_raw[$i]) - 1;
            }
        }
    }

    public function isLong() {
        return strpos($this->vowel, ":") !== false;
    }

    public function isDead() {
        return (empty($this->final) && !$this->isLong()) 
            || in_array($this->final, ['k', 't', 'p']);
    }

    public function isMid() {
        return in_array($this->initial, ['k', 'c', 'd', 't', 'b', 'p', 'q']);
    }

    public function toThai() {
        global $consonants, $vowels, $toneMarks, $finalConsonants;
        $init = $consonants[$this->initial];
        if($this->isMid()) {
            $init = $init[0];
            if($this->tone == 1 && $this->isDead()) {
                $toneMark = $toneMarks[0];
            } else {
                $toneMark = $toneMarks[$this->tone];
            }
        } else {
            $r = $this->getToneMarkAndClassForNonMid();
            $init = $init[$r[1]];
            $toneMark = $toneMarks[$r[0]];
        }
        if(!empty($this->secondInitial)) $init .= $consonants[$this->secondInitial][1];
        if($this->vowel === 'a' && $this->final === 'w') {
            $ret = $vowels['au'][0];
            $ret = str_replace('ก', $init, $ret);
        } else if($this->vowel === 'a' && $this->final === 'j') {
            $ret = $vowels['ai'][0];
            $ret = str_replace('ก', $init, $ret);
        } else if($this->vowel === 'a' && $this->final === 'm') {
            $ret = $vowels['am'][0];
            $ret = str_replace('ก', $init, $ret);
        } else if(($this->vowel === 'oe' || $this->vowel === 'oe:') && $this->final === 'j') {
            $ret = $vowels['oej'][0];
            $ret = str_replace('ก', $init, $ret);
        } else {
            $ret = $vowels[$this->vowel][empty($this->final) ? 0 : ($toneMark === "ก" ? 1 : 2)];
            $ret = str_replace('ก', $init, $ret);
            if(!empty($this->final)) $ret .= $finalConsonants[$this->final];
        }
        $ไม้เอก = str_replace('ก', '', 'ก่');
        $ret = str_replace($ไม้เอก, str_replace('ก', '', $toneMark), $ret);
        return $ret;
    }

    public function isToneIllegal() {
        return $this->isDead() && ($this->tone === 0 || $this->tone === 4);
    }

    public function toLoo() {
        $syl1 = clone $this;
        if($this->initial == 'l' || $this->initial == 'r') {
            $syl1->initial = 's';
        } else {
            $syl1->initial = 'l';
        }
        $syl1->secondInitial = '';

        $syl2 = clone $this;
        switch($this->vowel) {
            case 'u':
                $syl2->vowel = 'i';
            break;
            case 'u:':
                $syl2->vowel = 'i:';
            break;
            default:
                if($this->isLong()) {
                    $syl2->vowel = 'u:';
                } else {
                    $syl2->vowel = 'u';
                }
            break;
        }

        return [$syl1, $syl2];
    }

    private function getToneMarkAndClassForNonMid() { //[tonemark, class]
        switch($this->tone) {
            case 0:
                return [0, 1];
            case 1:
                if($this->isDead()) {
                    return [0, 0];
                } else {
                    return [1, 0];
                }
            case 2:
                if($this->isDead()) {
                    if($this->isLong()) {
                        return [0, 1];
                    } else {
                        return [1, 1];
                    }
                } else {
                    return [1, 1];
                }
            case 3:
                if($this->isDead()) {
                    if($this->isLong()) {
                        return [2, 1];
                    } else {
                        return [0, 1];
                    }
                } else {
                    return [2, 1];
                }
            case 4:
                return [0, 0];
        }
    }
}

function parseIPA(string $ipa) {
    $ipa = transformIPAChar($ipa);
    $ret = [];
    $syls_raw = explode(".", $ipa);
    foreach($syls_raw as $syl_raw) {
        if(empty(trim($syl_raw))) continue;
        $syl = new Syl($syl_raw);
        $ret[] = $syl;
    }
    return $ret;
}

function transformIPAChar($ipa) {
    $r = str_replace("ɯ", "ue", $ipa);
    $r = str_replace("ɛ", "ae", $r);
    $r = str_replace("ᴐ", "oa", $r);
    $r = str_replace("ɤ", "oe", $r);
    $r = str_replace("ŋ", "ng", $r);
    $r = str_replace("ʔ", "q", $r);
    $r = str_replace("ː", ":", $r);
    $r = str_replace("ʰ", "h", $r);
    $r = str_replace(" ", ".", $r);
    return $r;
}

function syllablesToThai($arr) {
    if(count(array_filter($arr, function($x) { return $x->isToneIllegal(); })) > 0) return '';
    $ret = '';
    foreach($arr as $x) {
        $ret .= $x->toThai();
    }
    return $ret;
}

function swapSyllables($arr, $swapTone = false, $omitFirstSyllable = false) {
    $a = [];
    foreach($arr as $x) {
        $a[] = clone $x;
    }

    if($omitFirstSyllable) {
        $first = $a[1];
    } else {
        $first = $a[0];
    }
    $last = $a[count($a) - 1];

    $first_vowel = $first->vowel;
    $first_final = $first->final;
    $last_vowel = $last->vowel;
    $last_final = $last->final;

    $first->vowel = $last_vowel;
    $first->final = $last_final;
    $last->vowel = $first_vowel;
    $last->final = $first_final;

    if($swapTone) {
        $first_tone = $first->tone;
        $last_tone = $last->tone;

        $first->tone = $last_tone;
        $last->tone = $first_tone;
    }

    return $a;
}

function looToThai($syl1, $syl2) {
    $r = new Syl();
    $r->initial = $syl2->initial;
    $r->secondInitial = $syl2->secondInitial;
    $r->vowel = $syl1->vowel;
    $r->final = $syl2->final;
    $r->tone = $syl2->tone;
    return $r;
}

function extractString($prefix, $suffix, $origin) {
    $begin = strpos($origin, $prefix) + strlen($prefix);
    $end = strpos($origin, $suffix, $begin);
    return substr($origin, strpos($origin, $prefix) + strlen($prefix), $end - $begin);
}

$input = $_GET['text'];
if(empty($input)) {
    die("<span style='color:darkred;'>โปรดกรอกข้อความ!</span>");
}

$url = "http://161.200.50.2/th2ipa";
/* STEP 1. let’s create a cookie file */
$ckfile = tempnam(dirname(__FILE__) . "/tmp/", "COOK");
/* STEP 2. visit the homepage to set the cookie properly */
$ch = curl_init ($url);
curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile); 
curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile); 
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
$ctnt = curl_exec ($ch);
$token = extractString('<input id="csrf_token" name="csrf_token" type="hidden" value="', '"', $ctnt);
curl_close($ch);

$ch = curl_init ($url);
$query = http_build_query([
    'csrf_token' => $token,
    'inputtxt' => trim($input),
    'submit' => 'RUN'
]);
curl_setopt ($ch, CURLOPT_COOKIEJAR, $ckfile); 
curl_setopt ($ch, CURLOPT_COOKIEFILE, $ckfile); 
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLOPT_POST, true);
//curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt ($ch, CURLOPT_POSTFIELDS, $query);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Length: " . strval(strlen($query)),
    "Content-Type: application/x-www-form-urlencoded"
]);
$ctnt = curl_exec ($ch);
//$information = curl_getinfo($ch);
//var_dump($information);
curl_close($ch);

if(strpos($ctnt, "Internal Server Error")) {
    die("<span style='color:darkred;'>ข้อมูลที่ป้อนเข้ามาไม่ถูกต้อง TLTK ไม่สามารถประมวลผลได้!้</span>");
}

$ipa = extractString('<textarea  id="Result" rows="5" cols="80">', '<', $ctnt);
$ipa = trim(str_replace("&lt;s/&gt;", '', $ipa));
$sylls = parseIPA($ipa);

switch($_GET['mode']) {
    case "thai2loo":
        foreach($sylls as $s) {
            echo(syllablesToThai($s->toLoo()));
        }
        break;
    case "loo2thai":
        $res = [];
        for($i=0; $i< floor(count($sylls) / 2); $i++) {
            $res[] = looToThai($sylls[2 * $i], $sylls[(2 * $i) + 1]);
        }
        echo(syllablesToThai($res));
        break;
    case "swap":
        if(count($sylls) < 2) die("<span style='color:darkred;'>จำนวนพยางค์ไม่เพียงพอ!</span>");

        echo("ไม่สลับวรรณยุกต์ ผวนทุกพยางค์: ");
        echo(syllablesToThai(swapSyllables($sylls, false, false)));
        echo("<hr>");
        echo("สลับวรรณยุกต์ ผวนทุกพยางค์: ");
        echo(syllablesToThai(swapSyllables($sylls, true, false)));
        if(count($sylls) > 2) {
            echo("<hr>");
            echo("ไม่สลับวรรณยุกต์ ละพยางค์แรก: ");
            echo(syllablesToThai(swapSyllables($sylls, false, true)));
            echo("<hr>");
            echo("สลับวรรณยุกต์ ละพยางค์แรก: ");
            echo(syllablesToThai(swapSyllables($sylls, true, true)));
        }
        break;
    case "regularize":
        echo syllablesToThai($sylls);
        break;
}
?>
</div>
<hr>
 Powered by TLTK 1.3.7 <a href="http://161.200.50.2/th2ipa">http://161.200.50.2/th2ipa</a>

<?php
ini_set("display_errors",true);
error_reporting(E_ALL);
require_once __DIR__."/configs.php";
require_once __DIR__."/functions.php";
include __DIR__."/strtohtml/simple_html_dom.php";

if(!isset($_POST['url']) || !isset($_POST['element'])){
    exit(json_encode(['error'=>'url & element values is required']));
}

if(!preg_match('#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i',$_POST['url'])){
    exit(json_encode(['error'=>'url is not url']));
}
if(!preg_match("/(br|basefont|hr|input|source|frame|param|area|meta|!--|col|link|option|base|img|wbr|!DOCTYPE)|(a|abbr|acronym|address|applet|article|aside|audio|b|bdi|bdo|big|blockquote|body|button|canvas|caption|center|cite|code|colgroup|command|datalist|dd|del|details|dfn|dialog|dir|div|dl|dt|em|embed|fieldset|figcaption|figure|font|footer|form|frameset|head|header|hgroup|h1|h2|h3|h4|h5|h6|html|i|iframe|ins|kbd|keygen|label|legend|li|map|mark|menu|meter|nav|noframes|noscript|object|ol|optgroup|output|p|pre|progress|q|rp|rt|ruby|s|samp|script|section|select|small|span|strike|strong|style|sub|summary|sup|table|tbody|td|textarea|tfoot|th|thead|time|title|tr|track|tt|u|ul|var|video)/i",$_POST['element'])){
    exit(json_encode(['error'=>'elelent is not HTML element']));
}
$url = $_POST['url'];
$element = $_POST['element'];
$domain = get_domain($url);
$pdo = db_connect();
$time=time();
$sqlForSelectFromCashe = "SELECT url.domain_id as domain_id,request.element_id as element_id,request.url_id as url_id,request.count as count,request.time as date,request.duration as duration,url.name as url,element.name as element,domain.name as domain FROM request 
INNER JOIN url ON request.url_id=url.id 
INNER JOIN domain ON url.domain_id = domain.id
INNER JOIN element on request.element_id=element.id
WHERE url.name=:url AND element.name=:element AND request.time>".($time-5*60);
$selectFromCashe = $pdo->prepare($sqlForSelectFromCashe);
if(!$selectFromCashe->execute([':url'=>$url,':element'=>$element])){
    echo $sqlForSelectFromCashe;
    exit(json_encode(['error'=>'error']));

}
if($selectFromCashe->rowCount()!=0){
    
    $data = $selectFromCashe->fetch();
    $domain_id=$data['domain_id'];
    $element_id=$data['element_id'];
    $url_id=$data['url_id'];

}else{
    $startTime = microtime(true);
    $html = get_data_from_url($url);
    if(!$html){
        echo "error";
    }
    $dom = str_get_html($html);
    $endtime=microtime(true);
    $count = count($dom->find($element));
    $duration = ($endtime-$startTime)*1000;


    $sqlForSelectElement = "SELECT id FROM element WHERE name=:element";
    $selectElement = $pdo->prepare($sqlForSelectElement);
    if(!$selectElement->execute([':element'=>$element])){
        exit(json_encode(['error'=>'error']));
    }
    if($selectElement->rowCount()!=0){
        $element_id = $selectElement->fetch()['id'];
    }else{
        $sqlForInsertElement = "INSERT INTO element (name) VALUES (:element)";
        $insertElement = $pdo->prepare($sqlForInsertElement);
        if(!$insertElement->execute([':element'=>$element])){
            exit(json_encode(['error'=>'error']));
        }
        $element_id = $pdo->lastInsertId();
    }


    $sqlForSelectDomain = "SELECT id FROM domain WHERE name=:domain";
    $selectDomain = $pdo->prepare($sqlForSelectDomain);
    if(!$selectDomain->execute([':domain'=>$domain])){
        exit(json_encode(['error'=>'error']));
    }
    if($selectDomain->rowCount()!=0){
        $domain_id = $selectDomain->fetch()['id'];
    }else{
        $sqlForInsertDomain = "INSERT INTO domain (name) VALUES (:domain)";
        $insertDomain = $pdo->prepare($sqlForInsertDomain);
        if(!$insertDomain->execute([':domain'=>$domain])){
            exit(json_encode(['error'=>'error']));
        }
        $domain_id = $pdo->lastInsertId();
    }



    $sqlForSelectDomain = "SELECT id FROM url WHERE name=:url";
    $selectDomain = $pdo->prepare($sqlForSelectDomain);
    if(!$selectDomain->execute([':url'=>$url])){
        exit(json_encode(['error'=>'error']));
    }
    if($selectDomain->rowCount()!=0){
        $url_id = $selectDomain->fetch()['id'];
    }else{
        $sqlForInsertUrl = "INSERT INTO url (name,domain_id) VALUES (:url,:domain)";
        $insertDomain = $pdo->prepare($sqlForInsertUrl);
        if(!$insertDomain->execute([':url'=>$url,':domain'=>$domain_id])){
            exit(json_encode(['error'=>'error']));
        }
        $url_id = $pdo->lastInsertId();
    }

    $sqlForInsertInCase = "INSERT INTO `request`( `url_id`, `element_id`, `count`, `duration`, `time`) VALUES  (:url,:element,:count,:duration,:time)";
    $insertRequestCashe = $pdo->prepare($sqlForInsertInCase);
    if(!$insertRequestCashe->execute([
        ":url"      => $url_id,
        ":element"  => $element_id,
        ":count"    => $count,
        ":duration" => $duration,
        ":time"     => $time,
    ])){
        exit(json_encode(['error'=>'error']));
    }
    $data = [
        "date" => $time,
        "domain"=> $domain,
        "duration"=> $duration,
        "element"=> $element,
        "url"=> $url,
        "count"=> $count,
    ];

}
$sqlForSelectUrlCount = "SELECT COUNT(id) as co from url where domain_id=:domin_id";
$selectUrlCount = $pdo->prepare($sqlForSelectUrlCount);
if(!$selectUrlCount->execute([':domin_id'=>$domain_id])){
    exit(json_encode(['error'=>'erroar']));

}
$urlCo = $selectUrlCount->fetch();
$data['urlCount']=$urlCo['co'];
$sqlForSelectAVGTime = "SELECT AVG(duration) as av FROM request 
                        INNER JOIN url ON request.url_id=url.id
                        WHERE url.domain_id=:domin_id AND time>".($time-24*60*60);
$selectAVGTime = $pdo->prepare($sqlForSelectAVGTime);
if(!$selectAVGTime->execute([':domin_id'=>$domain_id])){
    echo $sqlForSelectAVGTime;
    exit(json_encode(['error'=>'error']));

}
$avgTime = $selectAVGTime->fetch();

$data['avgduration'] = $avgTime['av'];

$sqlForSelectDomainElementsCount = "SELECT SUM(count) as su FROM request 
                        INNER JOIN url ON request.url_id=url.id
                        WHERE url.domain_id=:domin_id AND element_id=:element_id";
$selectDomainElementCount = $pdo->prepare($sqlForSelectDomainElementsCount);
if(!$selectDomainElementCount->execute([':domin_id'=>$domain_id,'element_id'=>$element_id])){
    exit(json_encode(['error'=>'error']));

}
$domainElementsCount = $selectDomainElementCount->fetch();
$data['domainElementsCountName'] = $domainElementsCount['su'];


$sqlForSelectElementCount = "SELECT SUM(count) as su FROM request 
                        WHERE element_id=:element_id";
$selectDomainElementCount = $pdo->prepare($sqlForSelectElementCount);
if(!$selectDomainElementCount->execute(['element_id'=>$element_id])){
    exit(json_encode(['error'=>'erroar']));

}
$elementCount = $selectDomainElementCount->fetch();
$data['elementCountName'] = $elementCount['su'];


exit(json_encode($data));

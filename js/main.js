$(document).ready(function(){
    $("#get_data_from_url").bind("click",getData);
});

function getData(){
    $("#url").removeClass("hasError");
    $("#element").removeClass("hasError");



    const urlRegex = /[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)?/gi;
    const htmlRegEx = /(br|basefont|hr|input|source|frame|param|area|meta|!--|col|link|option|base|img|wbr|!DOCTYPE)|(a|abbr|acronym|address|applet|article|aside|audio|b|bdi|bdo|big|blockquote|body|button|canvas|caption|center|cite|code|colgroup|command|datalist|dd|del|details|dfn|dialog|dir|div|dl|dt|em|embed|fieldset|figcaption|figure|font|footer|form|frameset|head|header|hgroup|h1|h2|h3|h4|h5|h6|html|i|iframe|ins|kbd|keygen|label|legend|li|map|mark|menu|meter|nav|noframes|noscript|object|ol|optgroup|output|p|pre|progress|q|rp|rt|ruby|s|samp|script|section|select|small|span|strike|strong|style|sub|summary|sup|table|tbody|td|textarea|tfoot|th|thead|time|title|tr|track|tt|u|ul|var|video)/i;
    let url = $("#url").val();
    let element = $("#element").val();
    hasError = false;
    if(url==="" || !urlRegex.test(url)){
        $("#url").addClass("hasError");
        hasError = true
    }
    if(element==="" || !htmlRegEx.test(element)){
        $("#element").addClass("hasError");
        hasError = true
    }

    if(hasError){
        return false;
    }

    $.ajax({
        url: "api/get_data_from_url.php",
        method: "POST",
        data: { url:url,element:element },
        dataType:"json",
    }).done(function(data){
        if(data.error){
            alert("field");
            //TODO
            return false;
        }
        $("#response").removeClass("dn");
        $("#url").html(data.url);
        $("#fethDate").html(data.date);    
        $("#duration").html(data.duration);    
        $(".elementName").html(data.element);    
        $("#apperedCount").html(data.count);    
        $("#urlCount").html(data.urlCount);    
        $(".domainName").html(data.domainName);    
        $("#avgduration").html(data.avgduration);    
        $("#domainElementsCountName").html(data.domainElementsCountName);    
        $("#totalElementCount").html(data.totalElementCount);    
        console.log(data);
    })
    .fail(function( jqXHR, textStatus ) {
        alert("filed");
        //TODO 
    });

    /*request.done(function( msg ) {
        console.log(msg);
        $("#response").removeClass("dn");
        $("#url").html(msg.url);
        $("#fethDate").html(msg.date);    
        $("#duration").html(msg.duration);    
        $(".elementName").html(msg.elementName);    
        $("#apperedCount").html(msg.count);    
        $("#urlCount").html(msg.urlCount);    
        $(".domainName").html(msg.domainName);    
        $("#avgduration").html(msg.avgduration);    
        $("#domainElementsCountName").html(msg.domainElementsCountName);    
        $("#totalElementCount").html(msg.totalElementCount);    
    });
       
      request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
      });*/


      return true;
}
<html>
    <head>
        <title></title>
        <link rel="stylesheet" type="text/css" href="./style/style.css">
        <script src="./js/jquery-3.5.1.min.js"></script>
        <script src="./js/main.js"></script>

    </head>
    <body>
        <div>
            <input type="text" id="url">
            <input type="text" id="element">
            <button id="get_data_from_url"> get data </button>
        </div>
        <div id="response" class="dn">

            <div id="report">
                URL <span id="url"></span> Fetched on <span id="fethDate"></span>, took <span id="duration"></span>msec.
                Element <<span class="elementName"></span>> appeared  <span id="apperedCount"></span> times in page.


            </div>

            <div id="staristic">
                General Statistics
                <span id="urlCount"></span> different URLs from <span class="domainName"></span> have been fetched
                Average fetch time from <span class="domainName"></span> during the last 24 hours hours is <span class="avgduration"></span>ms
                There was a total of <span id="domainElementsCountName"></span> <<span class="elementName"></span>> elements from <span class="domainName"></span>
                Total of <span id="totalElementCount"></span> <<span class="elementName"></span>> elements counted in all requests ever made.
            </div>
        </div>
    </body>
</html>
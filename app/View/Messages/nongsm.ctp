<html>
    <head>
        <script type="text/javascript" src="jquery.min.js"></script>

        <style type="text/css">
            span:hover {
                color: #aaa000;
            }
            .gsm, .not {
                font-size: 25pt;
                white-space: pre-wrap;
                display: inline-block;
                min-width: 20px;
            }
            .not {
                background-color: red;
                border: solid 1px #e44;
                text-decoration: underline;
            }
            #chardata {
                height: 130px;
                width: 400px;
                white-space: pre;
            }
            #output {
                width: 100%;
                text-wrap: normal;
            }

        </style>

        <script type="text/javascript">
            var GSMSet = (function() {

                    var gsm = [0x0040, 0x00A3, 0x0024, 0x00A5, 0x00E8, 0x00E9, 0x00F9, 0x00EC,
                    0x00F2, 0x00E7, 0x000A, 0x00D8, 0x00F8, 0x000D, 0x00C5, 0x00E5,
                    0x0394, 0x005F, 0x03A6, 0x0393, 0x039B, 0x03A9, 0x03A0, 0x03A8,
                    0x03A3, 0x0398, 0x039E, 0x000C, 0x005E, 0x007B, 0x007D, 0x005C,
                    0x005B, 0x007E, 0x005D, 0x007C, 0x20AC, 0x00C6, 0x00E6, 0x00DF, 0x00C9,
                    0x0020, 0x0021, 0x0022, 0x0023, 0x00A4, 0x0025, 0x0026, 0x0027, 0x0028,
                    0x0029, 0x002A, 0x002B, 0x002C, 0x002D, 0x002E, 0x002F, 0x0030, 0x0031,
                    0x0032, 0x0033, 0x0034, 0x0035, 0x0036, 0x0037, 0x0038, 0x0039, 0x003A,
                    0x003B, 0x003C, 0x003D, 0x003E, 0x003F, 0x00A1, 0x0041, 0x0391, 0x0042,
                    0x0392, 0x0043, 0x0044, 0x0045, 0x0395, 0x0046, 0x0047, 0x0048, 0x0397,
                    0x0049, 0x0399, 0x004A, 0x004B, 0x039A, 0x004C, 0x004D, 0x039C, 0x004E,
                    0x039D, 0x004F, 0x039F, 0x0050, 0x03A1, 0x0051, 0x0052, 0x0053, 0x0054,
                    0x0055, 0x0056, 0x0057, 0x0058, 0x0059, 0x005A, 0x00C4, 0x00D6, 0x00D1,
                    0x00DC, 0x00A7, 0x00BF, 0x0061, 0x0062, 0x0063, 0x0064, 0x0065, 0x0066,
                    0x0067, 0x0068, 0x0069, 0x006A, 0x006B, 0x006C, 0x006D, 0x006E, 0x006F,
                    0x0070, 0x0071, 0x0072, 0x0073, 0x0074, 0x0075, 0x0076, 0x0077, 0x0078,
                    0x0079, 0x007A, 0x00E4, 0x00F6, 0x00F1, 0x00FC, 0x00E0];

                    var o = new Object();

                    for(var i in gsm) {
                        o[gsm[i]] = true;
                    }
                    return o;

            })();

            function in_gsm(character) {
                return character.charCodeAt(0) in GSMSet;
            }
            function find_non_gsm(str) {
                accum = []
                    for (var i in str) {
                        var ch = str[i];
                        accum.push([ch, in_gsm(ch)]);
                    }
                return accum;
            }
            function render(char_bool) {
                var accum = "";
                for(var i in char_bool) {
                    var char = char_bool[i][0];
                    var gsm = char_bool[i][1];
                    var css = gsm ? "gsm" : "not";
                    accum += "<span class=" + css + ">" + char + "</span>";
                }
                return accum;
            }
            function test(evt) {
                var str = document.getElementById("input").value;
                var box = document.getElementById("output");
                output.innerHTML = render(find_non_gsm(str));
            }
            $(function() {
                $(".gsm, .not").live('mouseover', function () {
                    var url = "/characters/utf8/" + this.innerText + ".json";
                    $.get(url, function (resp) {
                        $("#chardata").html(resp.name + " <a href=\"" + url + "\">("+ resp.codepoint + ")</a>");
                    });
                });
            });
        </script>
    </head>
    <body>
        <div class="portlet box blue-dark">
			<div class="portlet-title">
				<div class="caption">
					NON-GSM
				</div>
			</div>
			<div class="portlet-body">
				<div class="m-heading-1 border-white m-bordered">
					<table class="table table-bordered table-striped">
						<tr>            
							<td style="width:15%"><b>
								Be careful not to copy and paste your SMS message from applications like MS Word, Outlook, etc... because it 	can insert non-GSM or unicode characters in the message inadvertently. Messages with one or more non-GSM characters are limited to only <u style="color:darkred">70 characters</u>. 
							</b></td>
						</tr>
						<tr>            
						<td style="width:15%"><b>
						Any SMS messages that include 1 or more non-GSM characters will be separated by the gateway into messages of 70 characters or less. Paste the text you're attempting to send below and the non-GSM characters will be highlighted in red for your reference. You can find out what characters are included in the GSM set <a href="http://en.wikipedia.org/wiki/GSM_03.38#GSM_7_bit_default_alphabet_and_extension_table_of_3GPP_TS_23.038_.2F_GSM_03.38" target="_blank" style="color:#ee4723">here</a>
						<b></td>

						</tr>
					</table>
					<div class="form-group">
					<textarea class="form-control" id="input" placeholder="... Paste Message Here ..." onkeyup="test()"></textarea>
					</div>
					<!--<div id="chardata" style="float:right"> </div>-->
					<br/><hr style="clear: both; "/>
						<table class="table table-bordered table-striped">
							<tr>            
							<td style="width:15%"><div id="output"></div></td>
							</tr>
						</table>
					
				</div>
			</div>
		</div>
    </body>
</html>
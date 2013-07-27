<style>
            body {
                background: -webkit-gradient(linear, left top, left bottom, from(rgb(0, 0, 0)), to(rgb(111, 111, 111))); /* Chromium/Chrome/Safari - all webkit based browsers */
                background: -moz-linear-gradient(top,  rgb(0, 0, 0),  rgb(111, 111, 111)); /* Firefox - all gecko based browsers. */
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=rgb(0, 0, 0), endColorstr=rgb(111, 111, 111)); /* Internet Explorer */
                background-image: -o-linear-gradient(rgb(0, 0, 0),rgb(111, 111, 111)); /* Opera */
                background-repeat:repeat-x, repeat-y;

                /*height: 1024px;*/
            }

            .info {
                color: #00529B;
                background: #BDE5F8;
            }

            .warning, .error, .info, .success {
                border: 1px solid;
                margin: 15px 0px;
                padding:15px 20px 15px 55px;
                width: 500px;	
                font: bold 12px verdana;
                -moz-box-shadow: 0 0 5px #888;
                -webkit-box-shadow: 0 0 5px#888;
                box-shadow: 0 0 5px #888;
                text-shadow: 2px 2px 2px #ccc;
                -webkit-border-radius: 15px;
                -moz-border-radius: 15px;
                border-radius: 15px;
                width: 92%;
            }

            .warning {
                color: #9F6000;
                background: #FEEFB3;
            }

            .error {
                color: rgb(27, 22, 2);
                background: #FFBABA;
            }

            .success {
                color: #4F8A10;
                background: #DFF2BF;
            }

            .msg {
                margin: 100px;
            }

            .content {
                background: rgb(255, 236, 236);
                margin: 0px auto;
                width: 960px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                -moz-box-shadow: 0px 0px 10px #422A20;
                -webkit-box-shadow: 0px 0px 10px #422A20;
                box-shadow: 0px 0px 10px #422A20;
                padding: 30px 40px;
                padding-top: 5px;
                color: black;
            }

            .err_header {
                margin-bottom: 5px;
                color: black;
                font-size: 35px;
            }

            .class_name {
                color: rgb(58, 1, 1);
            }

            .func_name {
                color: rgb(32, 32, 209)
            }
        </style>

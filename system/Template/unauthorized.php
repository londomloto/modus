<!DOCTYPE html>
<html>
<head>
    <title><?php echo $name; ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            padding: 0;
            margin: 0;
            font-family: Consolas, Monaco, sans-serif;
        }
        h3, h4, p {
            margin: 0;
            padding: 0;
        }
        
        .container {
            
        }
        .item {
            padding: 10px 0;
        }
        label {
            font-weight: bold;
            display: block;
        }
        .line {
            display: block;
            height: 1px;
            background-color: #ccc;
            margin: 5px 0;
        }
        .header {
            padding: 20px;
            border-bottom: solid 4px #D64652;
            background-color: #f44455;
            color: #fff;
        }
        .body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3><?php echo "{$code} {$name}"; ?></h3>
        </div>

        <div class="body">
            <div class="item">
                <p><?php echo $message; ?></p>
            </div>
        </div>
        
    </div>
</body>
</html>
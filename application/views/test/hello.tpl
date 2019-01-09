<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
HELLO {$name} test view!
<ul>
{foreach $list as $k=>$v}
    <li>{$k}--{$v}</li>
{/foreach}
</ul>
</body>
</html>
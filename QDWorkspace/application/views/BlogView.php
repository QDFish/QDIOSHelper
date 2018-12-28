/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/24
 * Time: 9:08 PM
 */

<html>
<head>
    <title><?php echo $title; ?></title>
</head>
<body>
    <h1><?php echo $content; ?></h1>

    <ul>
        <?php foreach ($todo_list as $item):?>
            <li><?php echo $item;?>
        <?php endforeach;?>
    </ul>
</body>
</html>

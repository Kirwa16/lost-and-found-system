<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../models/MatchEngine.php';

/*
|--------------------------------------------------------------------------
| REPORT LOST ITEM
|--------------------------------------------------------------------------
*/

if(isset($_POST['submit_lost_item']))
{
    $item = new Item();

    $userId = $_SESSION['user_id'];

    $itemName = trim($_POST['item_name']);

    $category =
        ($_POST['category'] === 'Other')
        ? trim($_POST['custom_category'])
        : $_POST['category'];

    $color = trim($_POST['color']);
    $brandModel = trim($_POST['brand_model']);
    $uniqueFeatures = trim($_POST['unique_features']);

    $description = trim($_POST['description']);
    $locationLost = trim($_POST['location_lost']);
    $dateLost = $_POST['date_lost'];

    $imageName = null;

    if(
        isset($_FILES['image']) &&
        $_FILES['image']['error'] === 0
    )
    {
        $imageName =
            time() . "_" .
            basename($_FILES['image']['name']);

        $uploadDir =
            __DIR__ .
            '/../uploads/lost-items/';

        if(!is_dir($uploadDir))
        {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadDir . $imageName
        );
    }

    $item->createLostItem(
        $userId,
        $itemName,
        $category,
        $color,
        $brandModel,
        $uniqueFeatures,
        $description,
        $locationLost,
        $dateLost,
        $imageName
    );

    $matcher = new MatchEngine();
    $matcher->generateMatches();

    $_SESSION['success'] =
        "Lost item reported successfully.";

    header("Location: /frontend/user/report-lost.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| REPORT FOUND ITEM
|--------------------------------------------------------------------------
*/

if(isset($_POST['submit_found_item']))
{
    $item = new Item();

    $userId = $_SESSION['user_id'];

    $itemName = trim($_POST['item_name']);

    $category =
        ($_POST['category'] === 'Other')
        ? trim($_POST['custom_category'])
        : $_POST['category'];

    $color = trim($_POST['color']);
    $brandModel = trim($_POST['brand_model']);
    $uniqueFeatures = trim($_POST['unique_features']);

    $description = trim($_POST['description']);
    $locationFound = trim($_POST['location_found']);
    $dateFound = $_POST['date_found'];

    $imageName = null;

    if(
        isset($_FILES['image']) &&
        $_FILES['image']['error'] === 0
    )
    {
        $imageName =
            time() . "_" .
            basename($_FILES['image']['name']);

        $uploadDir =
            __DIR__ .
            '/../uploads/found-items/';

        if(!is_dir($uploadDir))
        {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $uploadDir . $imageName
        );
    }

    $item->createFoundItem(
        $userId,
        $itemName,
        $category,
        $color,
        $brandModel,
        $uniqueFeatures,
        $description,
        $locationFound,
        $dateFound,
        $imageName
    );

    $matcher = new MatchEngine();
    $matcher->generateMatches();

    $_SESSION['success'] =
        "Found item reported successfully.";

    header("Location: /frontend/user/report-found.php");
    exit;
}
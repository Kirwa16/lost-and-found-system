<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /public/login.php");
    exit;
}

if($_SESSION['role'] !== 'user')
{
    header("Location: /frontend/admin/dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Report Found Item</title>

<link rel="stylesheet"
      href="/frontend/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/frontend/assets/css/forms.css">

<link rel="stylesheet"
      href="/frontend/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include '../components/user-sidebar.php'; ?>

    <div class="main">

        <div class="content">

            <h1>Report Found Item</h1>

            <?php if(isset($_SESSION['success'])): ?>

                <div class="success">

                    <?= htmlspecialchars($_SESSION['success']) ?>

                </div>

                <?php unset($_SESSION['success']); ?>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="error">

                    <?= htmlspecialchars($_SESSION['error']) ?>

                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>

            <div class="form-card">

                <form
                    action="/backend/controllers/ItemController.php"
                    method="POST"
                    enctype="multipart/form-data">

                    <div class="form-group">

                        <label>Item Name</label>

                        <input
                            type="text"
                            name="item_name"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Category</label>

                        <select
                            name="category"
                            id="category"
                            required>

                            <option value="">
                                Select Category
                            </option>

                            <option>Electronics</option>
                            <option>Documents</option>
                            <option>Keys</option>
                            <option>Clothing</option>
                            <option>Bags</option>
                            <option>Accessories</option>
                            <option>Other</option>

                        </select>

                    </div>

                    <div
                        class="form-group"
                        id="customCategory"
                        style="display:none;">

                        <label>Specify Category</label>

                        <input
                            type="text"
                            name="custom_category">

                    </div>

                    <div class="form-group">

                        <label>Color</label>

                        <input
                            type="text"
                            name="color"
                            placeholder="Black, Blue, Silver..."
                            required>

                    </div>

                    <div class="form-group">

                        <label>Brand / Model</label>

                        <input
                            type="text"
                            name="brand_model"
                            placeholder="Samsung Galaxy A15">

                    </div>

                    <div class="form-group">

                        <label>Unique Features</label>

                        <textarea
                            name="unique_features"
                            rows="4"
                            placeholder="Scratches, stickers, engravings, lock screen photo, special markings"></textarea>

                    </div>

                    <div class="form-group">

                        <label>Description</label>

                        <textarea
                            name="description"
                            rows="6"
                            placeholder="Provide detailed information about the item"
                            required></textarea>

                    </div>

                    <div class="form-group">

                        <label>Location Found</label>

                        <input
                            type="text"
                            name="location_found"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Date Found</label>

                        <input
                            type="date"
                            name="date_found"
                            max="<?= date('Y-m-d') ?>"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Upload Image</label>

                        <input
                            type="file"
                            name="image"
                            accept="image/*">

                        <img
                            id="preview"
                            style="
                                max-width:250px;
                                margin-top:15px;
                                display:none;
                                border-radius:10px;">

                    </div>

                    <button
                        type="submit"
                        name="submit_found_item"
                        class="action-btn">

                        Submit Report

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<script>

document
.getElementById("category")
.addEventListener("change", function(){

    document
    .getElementById("customCategory")
    .style.display =
        this.value === "Other"
        ? "block"
        : "none";
});

document
.querySelector('input[name="image"]')
.addEventListener('change', function(e){

    const file = e.target.files[0];

    if(file)
    {
        const preview =
            document.getElementById('preview');

        preview.src =
            URL.createObjectURL(file);

        preview.style.display = 'block';
    }
});

</script>

</body>

</html>
<?php

require_once '../includes/auth.php';
include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/forms.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>Report Found Item</h1>

        <?php
        if(isset($_SESSION['success']))
        {
            echo "<div class='success'>"
                . $_SESSION['success'] .
                "</div>";

            unset($_SESSION['success']);
        }
        ?>

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

                        <option>
                            Electronics
                        </option>

                        <option>
                            Documents
                        </option>

                        <option>
                            Keys
                        </option>

                        <option>
                            Clothing
                        </option>

                        <option>
                            Bags
                        </option>

                        <option>
                            Accessories
                        </option>

                        <option>
                            Other
                        </option>

                    </select>

                </div>

                <div
                    class="form-group"
                    id="customCategory"
                    style="display:none;">

                    <label>
                        Specify Category
                    </label>

                    <input
                        type="text"
                        name="custom_category">

                </div>

                <div class="form-group">

                    <label>Description</label>

                    <textarea
                        name="description"
                        rows="5"
                        required></textarea>

                </div>

                <div class="form-group">

                    <label>
                        Location Found
                    </label>

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
                        required>

                </div>

                <div class="form-group">

                    <label>
                        Upload Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept="image/*">

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

<script>

document
.getElementById("category")
.addEventListener("change", function(){

    let custom =
        document.getElementById(
            "customCategory"
        );

    custom.style.display =
        this.value === "Other"
        ? "block"
        : "none";
});

</script>

<?php include '../includes/footer.php'; ?>


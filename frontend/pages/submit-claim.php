<?php
session_start();

$itemName = $_GET['item'] ?? 'HP Laptop';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Claim</title>

    <link rel="stylesheet" href="../assets/css/claim.css">
</head>

<body>

<div class="container">

    <div class="claim-card">

        <h1>Submit Ownership Claim</h1>

        <p class="subtitle">
            Help us verify that this item belongs to you.
        </p>

        <div class="item-info">

            <h3>Claiming:</h3>
            <p><?php echo htmlspecialchars($itemName); ?></p>

        </div>

        <form>

            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    placeholder="Enter your full name"
                    required
                >

            </div>

            <div class="form-group">

                <label>Student / Staff ID</label>

                <input
                    type="text"
                    placeholder="e.g. 189720"
                    required
                >

            </div>

            <div class="form-group">

                <label>Proof of Ownership</label>

                <textarea
                    rows="6"
                    placeholder="Describe unique features, serial number, stickers, wallpaper, documents inside the item etc."
                    required
                ></textarea>

            </div>

            <div class="form-group">

                <label>Supporting Evidence (Optional)</label>

                <input type="file">

            </div>

            <button class="submit-btn">
                Submit Claim
            </button>

        </form>

    </div>

</div>

</body>

</html>
<?php

session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['student', 'staff'], true))
{
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/models/Search.php';

$searchModel = new Search();

function renderSearchRows(array $results, string $returnTo): string
{
    ob_start();
    $count = 1;

    foreach($results as $item):
        $itemType = ($item['item_type'] === 'Found Item') ? 'found' : 'lost';
        $canClaim = $itemType === 'found'
            && in_array($item['status'], ['available', 'pending'], true);
?>

                                <tr>

                                    <td>
                                        <?= $count++ ?>
                                    </td>

                                    <td>

                                        <?php if($item['item_type'] === 'Lost Item'): ?>

                                            <span class="badge badge-danger">
                                                Lost
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-success">
                                                Found
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['category']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['location']) ?>
                                    </td>

                                    <td>

                                        <?php if($item['status'] === 'matched'): ?>

                                            <span class="badge badge-success">
                                                Matched
                                            </span>

                                        <?php elseif($item['status'] === 'claimed'): ?>

                                            <span class="badge badge-warning">
                                                Claimed
                                            </span>

                                        <?php elseif($item['status'] === 'available'): ?>

                                            <span class="badge badge-success">
                                                Available
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-danger">
                                                Pending
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= date(
                                            'd M Y',
                                            strtotime($item['item_date'])
                                        ) ?>
                                    </td>

                                    <td>
                                        <div class="table-actions">
                                            <a
                                                href="/user/view-item-detail.php?id=<?= $item['id'] ?>&type=<?= $itemType ?>&return=<?= urlencode($returnTo) ?>"
                                                class="action-btn view"
                                                title="View Item"
                                                aria-label="View Item">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <?php if($canClaim): ?>

                                                <a
                                                    href="/user/submit-claim.php?item_id=<?= $item['id'] ?>&item_type=<?= $itemType ?>"
                                                    class="action-btn approve"
                                                    title="Claim Item"
                                                    aria-label="Claim Item">
                                                    <i class="fas fa-hand-holding-heart"></i>
                                                </a>

                                            <?php endif; ?>
                                        </div>
                                    </td>

                                </tr>

<?php
    endforeach;

    return ob_get_clean();
}

$results = [];
$keyword = '';
$category = '';

if (isset($_GET['search']))
{
    $keyword = trim($_GET['keyword'] ?? '');
    $category = trim($_GET['category'] ?? '');

    $results = $searchModel->searchItems(
        $keyword,
        $category
    );
}

if(isset($_GET['ajax']))
{
    header('Content-Type: application/json');

    $query = http_build_query([
        'search' => 1,
        'keyword' => $keyword,
        'category' => $category
    ]);

    echo json_encode([
        'count' => count($results),
        'rows' => renderSearchRows(
            $results,
            '/user/search.php?' . $query
        )
    ]);

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Search Items</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>

<div class="user-layout">

    <?php include __DIR__ . '/../components/user-sidebar.php'; ?>

    <div class="main" id="main">

        <?php include __DIR__ . '/../components/topbar-user.php'; ?>

        <div class="content">

            <h1>Search Items</h1>

            <div class="form-card">

                <form method="GET" id="searchForm">

                    <div class="form-group">

                        <label>Keyword</label>

                        <input
                            type="text"
                            name="keyword"
                            id="keywordInput"
                            placeholder="Item name, description or location"
                            autocomplete="off"
                            value="<?= htmlspecialchars($keyword) ?>">

                    </div>

                    <div class="form-group">

                        <label>Category</label>

                        <select name="category" id="categoryInput">

                            <option value="">All Categories</option>

                            <option value="Electronics" <?= $category === 'Electronics' ? 'selected' : '' ?>>
                                Electronics
                            </option>

                            <option value="Documents" <?= $category === 'Documents' ? 'selected' : '' ?>>
                                Documents
                            </option>

                            <option value="Keys" <?= $category === 'Keys' ? 'selected' : '' ?>>
                                Keys
                            </option>

                            <option value="Clothing" <?= $category === 'Clothing' ? 'selected' : '' ?>>
                                Clothing
                            </option>

                            <option value="Bags" <?= $category === 'Bags' ? 'selected' : '' ?>>
                                Bags
                            </option>

                            <option value="Accessories" <?= $category === 'Accessories' ? 'selected' : '' ?>>
                                Accessories
                            </option>

                        </select>

                    </div>

                    <button
                        type="submit"
                        name="search"
                        class="action-btn">
                        Search
                    </button>

                </form>

            </div>

            <br>

                <div
                    class="card"
                    id="searchResultsCard"
                    style="<?= isset($_GET['search']) ? '' : 'display:none;' ?>">

                        <table class="table">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>

                            </thead>

                            <tbody id="searchResultsBody">

                            <?= renderSearchRows($results, $_SERVER['REQUEST_URI']) ?>

                            </tbody>

                        </table>

                        <p
                            id="noSearchResults"
                            style="<?= empty($results) && isset($_GET['search']) ? '' : 'display:none;' ?>">
                            No items found.
                        </p>

                </div>

        </div>

    </div>

</div>

<script src="/assets/js/sidebar.js"></script>
<script>
const searchForm = document.getElementById('searchForm');
const keywordInput = document.getElementById('keywordInput');
const categoryInput = document.getElementById('categoryInput');
const resultsCard = document.getElementById('searchResultsCard');
const resultsBody = document.getElementById('searchResultsBody');
const noResults = document.getElementById('noSearchResults');
let searchTimer;
let activeSearch;

function runLiveSearch(){
    const keyword = keywordInput.value.trim();
    const category = categoryInput.value;

    if(keyword === '' && category === ''){
        resultsCard.style.display = 'none';
        resultsBody.innerHTML = '';
        noResults.style.display = 'none';
        history.replaceState({}, '', '/user/search.php');
        return;
    }

    const params = new URLSearchParams({
        search: '1',
        ajax: '1',
        keyword: keyword,
        category: category
    });

    if(activeSearch){
        activeSearch.abort();
    }

    activeSearch = new AbortController();

    fetch('/user/search.php?' + params.toString(), {
        signal: activeSearch.signal
    })
    .then(function(response){
        return response.json();
    })
    .then(function(data){
        resultsCard.style.display = '';
        resultsBody.innerHTML = data.rows;
        noResults.style.display = data.count > 0 ? 'none' : '';

        params.delete('ajax');
        history.replaceState({}, '', '/user/search.php?' + params.toString());
    })
    .catch(function(error){
        if(error.name !== 'AbortError'){
            console.error(error);
        }
    });
}

function scheduleLiveSearch(){
    clearTimeout(searchTimer);
    searchTimer = setTimeout(runLiveSearch, 180);
}

keywordInput.addEventListener('input', scheduleLiveSearch);
categoryInput.addEventListener('change', runLiveSearch);

searchForm.addEventListener('submit', function(event){
    event.preventDefault();
    runLiveSearch();
});
</script>
</body>

</html>

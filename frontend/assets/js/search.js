function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    if(tab === 'search') {
        document.getElementById('searchTab').style.display = 'block';
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
    } else {
        document.getElementById('myClaimsTab').style.display = 'block';
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
        fetchMyClaims();
    }
}

async function fetchItems() {
    const keyword = document.getElementById('searchKeyword').value;
    const category = document.getElementById('searchCategory').value;
    const grid = document.getElementById('resultsGrid');
    grid.innerHTML = '<p style="text-align:center;">Loading...</p>';

    try {
        // Francis's backend should accept these as query params: api/get_items.php?keyword=x&category=y
        const res = await fetch(`api/get_items.php?keyword=${encodeURIComponent(keyword)}&category=${encodeURIComponent(category)}`);
        const data = await res.json();

        if(data.success && data.items.length > 0) {
            grid.innerHTML = data.items.map(item => `
                <div class="action-card" style="text-align: left;">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <h3>${item.item_name}</h3>
                        <span class="badge ${item.status === 'Found' ? 'verified' : 'pending'}">${item.status}</span>
                    </div>
                    <p style="font-size: 0.9rem; color: #666; margin: 0.5rem 0;">${item.description.substring(0, 100)}...</p>
                    <p style="font-size: 0.85rem;"><strong>Location:</strong> ${item.location} | <strong>Date:</strong> ${item.date}</p>
                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                        <button class="btn btn-outline btn-sm" onclick="alert('View details feature coming soon')">View Details</button>
                        <button class="btn btn-sm" onclick="openClaimModal('${item.item_id}', '${item.item_name}')">Submit Claim</button>
                    </div>
                </div>
            `).join('');
        } else {
            grid.innerHTML = '<p style="text-align: center; color: #666;">No items found matching your criteria.</p>';
        }
    } catch (err) {
        grid.innerHTML = '<p style="text-align: center; color: var(--danger);">Error loading items.</p>';
    }
}

function openClaimModal(itemId, itemName) {
    document.getElementById('claimItemId').value = itemId;
    document.getElementById('claimItemName').textContent = `Claiming: ${itemName}`;
    document.getElementById('claimModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('claimModal').style.display = 'none';
}

document.getElementById('claimForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    btn.disabled = true; btn.textContent = 'Submitting...';

    try {
        const res = await fetch('api/submit_claim.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                item_id: document.getElementById('claimItemId').value,
                proof: document.getElementById('proof').value
            })
        });
        const data = await res.json();
        if(res.ok && data.success) {
            showToast('Claim submitted for admin verification!', 'success');
            closeModal();
            e.target.reset();
        } else {
            showToast(data.message || 'Failed to submit claim', 'error');
        }
    } catch (err) {
        showToast('Network error.', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Submit Claim';
    }
});

async function fetchMyClaims() {
    const tbody = document.getElementById('myClaimsTable');
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading...</td></tr>';
    try {
        const res = await fetch('api/get_my_claims.php'); // Francis to implement
        const data = await res.json();
        if(data.success) {
            tbody.innerHTML = data.claims.map(claim => `
                <tr>
                    <td>${claim.item_name}</td>
                    <td>${claim.submission_date}</td>
                    <td><span class="badge ${claim.status.toLowerCase()}">${claim.status}</span></td>
                    <td>-</td>
                </tr>
            `).join('');
        }
    } catch(err) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color: var(--danger);">Error loading claims.</td></tr>';
    }
}
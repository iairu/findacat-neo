@props(['cat' => null])

<div class="pedigree-tools animate-slideInUp">
    @if($cat)
    <a href="{{ route('cats.tree', ['cat' => $cat->id, 'generations' => 5]) }}"
       class="pedigree-tool-btn"
       data-tooltip="View full family tree"
       data-tooltip-position="bottom">
        🌳 Family Tree
    </a>

    <a href="{{ route('cats.chart', $cat->id) }}"
       class="pedigree-tool-btn"
       data-tooltip="View pedigree chart"
       data-tooltip-position="bottom">
        📊 Pedigree Chart
    </a>

    @if($cat->dod)
    <a href="{{ route('cats.death', $cat->id) }}"
       class="pedigree-tool-btn"
       data-tooltip="Memorial information"
       data-tooltip-position="bottom">
        🕯️ Memorial
    </a>
    @endif

    <button type="button"
            class="pedigree-tool-btn"
            onclick="printPedigree()"
            data-tooltip="Print pedigree"
            data-tooltip-position="bottom">
        🖨️ Print
    </button>

    <button type="button"
            class="pedigree-tool-btn"
            onclick="sharePedigree('{{ $cat->full_name }}', '{{ route('cats.show', $cat->id) }}')"
            data-tooltip="Share this cat"
            data-tooltip-position="bottom">
        🔗 Share
    </button>

    <button type="button"
            class="pedigree-tool-btn"
            onclick="compareCat('{{ $cat->id }}')"
            data-tooltip="Compare with another cat"
            data-tooltip-position="bottom">
        🔀 Compare
    </button>
    @endif
</div>

<script>
function printPedigree() {
    window.print();
    toast.success('Printing pedigree...');
}

function sharePedigree(name, url) {
    if (navigator.share) {
        navigator.share({
            title: `${name} - Pedigree`,
            url: url
        }).then(() => {
            toast.success('Shared successfully!');
        }).catch((error) => {
            copyPedigreeLink(url);
        });
    } else {
        copyPedigreeLink(url);
    }
}

function copyPedigreeLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        toast.success('Link copied to clipboard!');
    }).catch(() => {
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        toast.success('Link copied to clipboard!');
    });
}

function compareCat(catId) {
    // Store cat for comparison
    localStorage.setItem('compare_cat_1', catId);
    toast.info('Cat selected for comparison. Now select another cat to compare.');

    // Add visual indicator
    document.body.classList.add('comparison-mode');

    // Show comparison mode notification
    const notification = document.createElement('div');
    notification.className = 'comparison-notification';
    notification.innerHTML = `
        <div style="background: var(--gradient-primary); color: white; padding: 15px 20px; border-radius: 10px; position: fixed; top: 80px; right: 20px; z-index: 1000; box-shadow: var(--shadow-lg);">
            <strong>Comparison Mode Active</strong><br>
            <small>Select another cat to compare</small><br>
            <button onclick="cancelComparison()" style="margin-top: 10px; background: white; color: var(--primary); border: none; padding: 5px 15px; border-radius: 5px; font-weight: 600; cursor: pointer;">
                Cancel
            </button>
        </div>
    `;
    document.body.appendChild(notification);
}

function cancelComparison() {
    localStorage.removeItem('compare_cat_1');
    document.body.classList.remove('comparison-mode');
    document.querySelector('.comparison-notification')?.remove();
    toast.info('Comparison cancelled');
}
</script>

<style>
body.comparison-mode::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(108, 92, 231, 0.1);
    pointer-events: none;
    z-index: 999;
}
</style>

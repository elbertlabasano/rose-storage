    var listingPage = 1;
    var listingRequest = 0;
    var searchDelay;

    function fileIcon(symbol) {
        var icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        icon.setAttribute('aria-hidden', 'true');
        var use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
        use.setAttribute('href', '#' + symbol); icon.appendChild(use);
        return icon;
    }

    async function loadBuckets() {
        try {
            var response = await api('/buckets');
            var page = response.data || {};
            var buckets = page.data || [];
            document.getElementById('bucketCount').textContent = page.total || buckets.length;
            bucketList.textContent = '';
            if (!buckets.length) {
                document.getElementById('bucketIntegration').hidden = true;
                var empty = document.createElement('p'); empty.className = 'bucket-sidebar-empty';
                empty.textContent = 'No buckets yet. Create your first one below.';
                bucketList.appendChild(empty);
                objectEmpty.classList.remove('hidden'); document.getElementById('objectTable').hidden = true;
                return;
            }
            buckets.forEach(function (bucket) {
                var button = document.createElement('button'); button.type = 'button'; button.dataset.id = bucket.id;
                button.className = 'bucket-list-item'; button.title = bucket.name;
                button.appendChild(fileIcon('icon-box'));
                var label = document.createElement('span'); label.textContent = bucket.name;
                var count = document.createElement('small'); count.textContent = bucket.objects_count || 0;
                button.appendChild(label); button.appendChild(count);
                button.addEventListener('click', function () { selectBucket(bucket.id, bucket.name); });
                bucketList.appendChild(button);
            });
            var current = buckets.find(function (bucket) { return bucket.id === selectedBucket; }) || buckets[0];
            await selectBucket(current.id, current.name);
        } catch (error) { setStatus(appStatus, error.message, true); }
    }

    async function selectBucket(id, name, page) {
        if (!id) return;
        if (selectedBucket !== id) {
            document.getElementById('objectSearch').value = '';
            document.getElementById('objectVisibility').value = 'all';
            document.getElementById('objectSort').value = 'newest';
        }
        selectedBucket = id;
        listingPage = page || 1;
        var request = ++listingRequest;
        document.getElementById('bucketTitle').textContent = name;
        document.getElementById('selectedBucketId').textContent = id;
        document.getElementById('bucketUploadEndpoint').textContent = '/api/buckets/' + encodeURIComponent(id) + '/objects';
        document.getElementById('bucketIntegration').hidden = false;
        document.getElementById('uploadForm').classList.remove('hidden');
        Array.from(bucketList.children).forEach(function (button) {
            var active = button.dataset.id === id;
            button.classList.toggle('is-selected', active);
            if (active) button.setAttribute('aria-current', 'true'); else button.removeAttribute('aria-current');
        });
        var search = document.getElementById('objectSearch').value.trim();
        var visibility = document.getElementById('objectVisibility').value;
        var query = new URLSearchParams({ search: search, visibility: visibility, sort: document.getElementById('objectSort').value, page: String(listingPage) });
        document.getElementById('fileLoading').hidden = false;
        document.getElementById('fileListError').hidden = true;
        document.getElementById('objectTable').hidden = true;
        objectEmpty.classList.add('hidden');
        document.getElementById('previousObjectPage').disabled = true;
        document.getElementById('nextObjectPage').disabled = true;
        try {
            var response = await api('/buckets/' + encodeURIComponent(id) + '?' + query.toString());
            if (request !== listingRequest) return;
            var data = response.data;
            var objects = data.objects || [];
            var pagination = data.pagination || { current_page: 1, last_page: 1, total: objects.length, from: objects.length ? 1 : 0, to: objects.length };
            listingPage = pagination.current_page;
            if (!objects.length && listingPage > 1 && pagination.total > 0) {
                return selectBucket(id, name, pagination.last_page);
            }
            document.getElementById('statFiles').textContent = Number(data.objects_count).toLocaleString();
            document.getElementById('statStorage').textContent = formatBytes(data.bytes || 0);
            document.getElementById('statPrivate').textContent = Number(data.private_count || 0).toLocaleString();
            document.getElementById('bucketHint').textContent = search || visibility !== 'all' ? pagination.total + ' matching files in this bucket' : 'Everything in ' + name + ', in one place.';
            document.getElementById('filePageSummary').textContent = pagination.total ? (pagination.from || 1) + '–' + pagination.to + ' of ' + pagination.total + ' files' : '0 files';
            document.getElementById('objectPageNumber').textContent = 'Page ' + listingPage + ' of ' + Math.max(1, pagination.last_page);
            document.getElementById('previousObjectPage').disabled = listingPage <= 1;
            document.getElementById('nextObjectPage').disabled = listingPage >= pagination.last_page;
            document.getElementById('objectEmptyTitle').textContent = search || visibility !== 'all' ? 'No files match your search.' : 'A little room for your next file.';
            document.getElementById('objectEmptyHint').textContent = search || visibility !== 'all' ? 'Try another name or clear the filters to see all files.' : 'Choose a file above to get this bucket started.';
            document.getElementById('clearFileFilters').hidden = !search && visibility === 'all';
            renderObjects(objects);
        } catch (error) {
            if (request !== listingRequest) return;
            var message = document.getElementById('fileListError'); message.textContent = 'Could not load files. ' + error.message;
            message.hidden = false;
        } finally {
            if (request === listingRequest) document.getElementById('fileLoading').hidden = true;
        }
    }

    function addCell(row, value, className) {
        var cell = document.createElement('td'); cell.className = className || '';
        if (value instanceof Node) cell.appendChild(value); else cell.textContent = value;
        row.appendChild(cell); return cell;
    }

    function fileDescription(object) {
        var extension = (object.name.split('.').pop() || '').toLowerCase();
        var names = { doc: 'Word document', docx: 'Word document', pdf: 'PDF document', xls: 'Spreadsheet', xlsx: 'Spreadsheet', csv: 'CSV spreadsheet', txt: 'Text document', zip: 'ZIP archive', mp4: 'Video', mp3: 'Audio', apk: 'Android application' };
        if ((object.mime_type || '').indexOf('image/') === 0) return extension.toUpperCase() + ' image';
        return names[extension] || (extension.length <= 6 ? extension.toUpperCase() + ' file' : 'File');
    }

    function renderObjects(objects) {
        objectTableBody.textContent = '';
        objectEmpty.classList.toggle('hidden', objects.length > 0);
        document.getElementById('objectTable').hidden = !objects.length;
        objects.forEach(function (object) {
            var row = document.createElement('tr');
            var nameWrap = document.createElement('div'); nameWrap.className = 'file-name-wrap';
            var thumb = document.createElement('div');
            var imageType = (object.mime_type || '').indexOf('image/') === 0;
            thumb.className = 'file-thumbnail ' + (imageType ? 'file-thumbnail-image' : 'file-thumbnail-document');
            var extension = object.name.split('.').pop().toUpperCase();
            var badge = /^[A-Z0-9]{1,5}$/.test(extension) ? extension : 'FILE';
            if (imageType && object.public_url) {
                var image = document.createElement('img'); image.src = object.public_url; image.alt = ''; image.loading = 'lazy';
                image.addEventListener('error', function () { thumb.textContent = badge; });
                thumb.appendChild(image);
            } else { thumb.textContent = badge; }
            var details = document.createElement('div'); details.className = 'file-name-details';
            var name = document.createElement('p'); name.className = 'file-name'; name.title = object.name; name.textContent = object.name;
            var description = document.createElement('p'); description.className = 'file-description'; description.title = object.mime_type || '';
            description.textContent = fileDescription(object);
            var mobileSize = document.createElement('span'); mobileSize.className = 'file-mobile-size'; mobileSize.textContent = ' · ' + formatBytes(object.size);
            description.appendChild(mobileSize);
            details.appendChild(name); details.appendChild(description); nameWrap.appendChild(thumb); nameWrap.appendChild(details);
            addCell(row, nameWrap, 'file-name-cell');
            var access = document.createElement('span'); access.className = 'file-access file-access-' + (object.visibility === 'public' ? 'public' : 'private');
            access.appendChild(fileIcon(object.visibility === 'public' ? 'icon-link' : 'icon-lock'));
            var accessLabel = document.createElement('span'); accessLabel.textContent = object.visibility === 'public' ? 'Public' : 'Private'; access.appendChild(accessLabel);
            addCell(row, access, 'file-access-cell');
            addCell(row, formatBytes(object.size), 'file-size-cell');
            var date = addCell(row, formatDate(object.created_at), 'file-date-cell');
            if (object.created_at) date.title = new Date(object.created_at).toLocaleString('en-PH', { timeZone: 'Asia/Manila' });
            var actions = document.createElement('div'); actions.className = 'file-row-actions';
            var download = document.createElement('button'); download.type = 'button'; download.className = 'file-icon-button';
            download.title = 'Download ' + object.name; download.setAttribute('aria-label', download.title); download.appendChild(fileIcon('icon-download'));
            download.addEventListener('click', function () { downloadObject(object); });
            var copy = document.createElement('button'); copy.type = 'button'; copy.className = 'file-icon-button';
            copy.title = object.visibility === 'public' ? 'Copy public link' : 'Copy private link (expires in 15 minutes)';
            copy.setAttribute('aria-label', copy.title + ': ' + object.name); copy.appendChild(fileIcon('icon-link'));
            copy.addEventListener('click', function () { copyObjectUrl(object); });
            actions.appendChild(download); actions.appendChild(copy); addCell(row, actions, 'file-actions-cell');
            objectTableBody.appendChild(row);
        });
    }

    function refreshFileListing(page) { if (selectedBucket) selectBucket(selectedBucket, document.getElementById('bucketTitle').textContent, page || 1); }
    async function copyBucketDetail(text, label) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                var temporary = document.createElement('textarea');
                temporary.value = text; temporary.style.cssText = 'position:fixed;opacity:0;width:1px;height:1px';
                document.body.appendChild(temporary);
                try {
                    temporary.select();
                    if (!document.execCommand('copy')) throw new Error('Clipboard unavailable');
                } finally { temporary.remove(); }
            }
            setStatus(appStatus, label + ' copied.', false);
        } catch (error) { setStatus(appStatus, 'Could not copy. Select and copy the displayed value instead.', true); }
    }
    document.getElementById('copyBucketId').addEventListener('click', function () { if (selectedBucket) copyBucketDetail(selectedBucket, 'Bucket ID'); });
    document.getElementById('copyBucketUploadUrl').addEventListener('click', function () { if (selectedBucket) copyBucketDetail(location.origin + document.getElementById('bucketUploadEndpoint').textContent, 'Upload API URL'); });
    document.getElementById('objectSearch').addEventListener('input', function () { clearTimeout(searchDelay); searchDelay = setTimeout(function () { refreshFileListing(); }, 250); });
    document.getElementById('objectVisibility').addEventListener('change', function () { refreshFileListing(); });
    document.getElementById('objectSort').addEventListener('change', function () { refreshFileListing(); });
    document.getElementById('previousObjectPage').addEventListener('click', function () { refreshFileListing(listingPage - 1); });
    document.getElementById('nextObjectPage').addEventListener('click', function () { refreshFileListing(listingPage + 1); });
    document.getElementById('refreshObjects').addEventListener('click', function () { refreshFileListing(listingPage); });
    document.getElementById('clearFileFilters').addEventListener('click', function () { document.getElementById('objectSearch').value = ''; document.getElementById('objectVisibility').value = 'all'; refreshFileListing(); });

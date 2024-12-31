<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "
    SELECT id, nama, jenis_kelamin, generasi
    FROM anggota 
    WHERE generasi = 1
    ORDER BY id
";
$result = $conn->query($query);

function getChildren($conn, $parentId, $gender) {
    $childrenQuery = "";
    if ($gender === 'Laki-laki') {
        $childrenQuery = "
            SELECT 
                a.*,
                GROUP_CONCAT(DISTINCT istri.id) as istri_ids,
                GROUP_CONCAT(DISTINCT istri.nama ORDER BY 
                    CASE 
                        WHEN istri.id = a.id_istri_1 THEN 1
                        WHEN istri.id = a.id_istri_2 THEN 2
                        WHEN istri.id = a.id_istri_3 THEN 3
                    END
                ) as nama_istri,
                GROUP_CONCAT(DISTINCT istri.foto ORDER BY 
                    CASE 
                        WHEN istri.id = a.id_istri_1 THEN 1
                        WHEN istri.id = a.id_istri_2 THEN 2
                        WHEN istri.id = a.id_istri_3 THEN 3
                    END
                ) as foto_istri,
                (SELECT GROUP_CONCAT(c.nama SEPARATOR ', ')
                 FROM anggota c
                 WHERE c.id_ayah = a.id) as anak
            FROM anggota a
            LEFT JOIN anggota istri ON (a.id_istri_1 = istri.id OR a.id_istri_2 = istri.id OR a.id_istri_3 = istri.id)
            WHERE a.id_ayah = ?
            GROUP BY a.id
            ORDER BY a.id
        ";
    } else {
        $childrenQuery = "
            SELECT 
                a.*,
                suami.id as suami_id,
                suami.nama as nama_suami,
                suami.foto as foto_suami,
                (SELECT GROUP_CONCAT(c.nama SEPARATOR ', ')
                 FROM anggota c
                 WHERE c.id_ibu = a.id) as anak
            FROM anggota a
            LEFT JOIN anggota suami ON (suami.id_istri_1 = a.id OR suami.id_istri_2 = a.id OR suami.id_istri_3 = a.id)
            WHERE a.id_ibu = ?
            GROUP BY a.id
            ORDER BY a.id
        ";
    }
    
    $stmt = $conn->prepare($childrenQuery);
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $children = [];
    while ($row = $result->fetch_assoc()) {
        if ($gender === 'Laki-laki' && !empty($row['nama_istri'])) {
            $row['nama_istri'] = $row['nama_istri'];
            $row['foto_istri'] = $row['foto_istri'];
            $row['istri_ids'] = explode(',', $row['istri_ids']);
        }
        $children[] = $row;
    }
    $stmt->close();
    return $children;
}

function renderFamilyTree($conn, $member) {
    $output = '<li>';
    $output .= '<div class="tree-card bg-white rounded-lg shadow-md">';
    $output .= '<div class="couple-container">';

    $output .= '<div class="person-info">';
    $output .= '<img src="' . ($member['jenis_kelamin'] === 'Laki-laki' ? '../../assets/img/default_male.jpg' : '../../assets/img/default_female.jpg') . '"
                     alt="' . htmlspecialchars($member['nama']) . '"
                     class="w-16 h-16 rounded-full mb-2">';
    $output .= '<h3 class="font-semibold">' . htmlspecialchars($member['nama']) . '</h3>';
    $output .= '<p class="text-sm text-gray-600">Sundut ' . $member['generasi'] . '</p>';
    $output .= '</div>';

    if ($member['jenis_kelamin'] === 'Laki-laki' && !empty($member['nama_istri'])) {
        $output .= '<div class="divider"></div>';
        $output .= '<div class="person-info">';
        $output .= '<img src="../../assets/img/default_female.jpg"
                         alt="' . htmlspecialchars($member['nama_istri']) . '"
                         class="w-16 h-16 rounded-full mb-2">';
        $output .= '<h3 class="font-semibold">' . htmlspecialchars($member['nama_istri']) . '</h3>';
        $output .= '<p class="text-sm text-gray-600">Istri</p>';
        $output .= '</div>';
    } elseif ($member['jenis_kelamin'] === 'Perempuan' && !empty($member['nama_suami'])) {
        $output .= '<div class="divider"></div>';
        $output .= '<div class="person-info">';
        $output .= '<img src="../../assets/img/default_male.jpg"
                         alt="' . htmlspecialchars($member['nama_suami']) . '"
                         class="w-16 h-16 rounded-full mb-2">';
        $output .= '<h3 class="font-semibold">' . htmlspecialchars($member['nama_suami']) . '</h3>';
        $output .= '<p class="text-sm text-gray-600">Suami</p>';
        $output .= '</div>';
    }
    
    $output .= '</div></div>';

    $children = getChildren($conn, $member['id'], $member['jenis_kelamin']);
    if (!empty($children)) {
        $output .= '<ul>';
        foreach ($children as $child) {
            $output .= renderFamilyTree($conn, $child);
        }
        $output .= '</ul>';
    }
    
    $output .= '</li>';
    return $output;
}

$allMembersQuery = "SELECT id, nama, generasi FROM anggota ORDER BY id";
$allMembers = $conn->query($allMembersQuery);
$membersList = [];
while ($member = $allMembers->fetch_assoc()) {
    $membersList[] = $member;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual Tarombo - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .tree-container {
            width: 100%;
            height: calc(100vh - 160px);
            overflow: hidden;
            position: relative;
            background: #f3f4f6;
        }
        .tree-wrapper {
            transform-origin: 0 0;
            transition: transform 0.1s ease;
            position: absolute;
            padding: 2rem;
        }
        .tree {
            min-height: 100vh;
            padding: 2rem;
        }
        .tree ul {
            padding-top: 20px;
            position: relative;
            display: flex;
            justify-content: center;
        }
        .tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
        }
        .tree li::before,
        .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #ccc;
            width: 50%;
            height: 20px;
        }
        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #ccc;
        }
        .tree li:only-child::after,
        .tree li:only-child::before {
            display: none;
        }
        .tree li:first-child::before,
        .tree li:last-child::after {
            border: 0 none;
        }
        .tree li:last-child::before {
            border-right: 2px solid #ccc;
            border-radius: 0 5px 0 0;
        }
        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }
        .tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 2px solid #ccc;
            width: 0;
            height: 20px;
        }
        .tree-card {
            min-width: 300px;
            margin: 0 10px;
        }
        .couple-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 0.5rem;
        }
        .person-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .divider {
            height: 80px;
            width: 2px;
            background-color: #ccc;
            margin: 0 1rem;
        }
        .zoom-controls {
            position: fixed;
            bottom: 80px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 40px;
        }
        .zoom-button {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: white;
            cursor: pointer;
            transition: background-color 0.2s;
            padding: 0;
        }
        .zoom-button:hover {
            background-color: #f3f4f6;
        }
        .zoom-level {
            width: 40px;
            padding: 8px 0;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        .search-container {
            position: relative;
            max-width: 250px;
            width: 100%;
        }
        @media (max-width: 640px) {
            .search-container {
                max-width: 180px;
            }
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
        }
        .search-result-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .search-result-item:hover {
            background-color: #f3f4f6;
        }
        .highlight {
            background-color: #fef08a;
            transition: background-color 0.5s ease;
        }
    </style>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="tarombo.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded"><</a>
                <h1 class="text-xl font-semibold">Tarombo</h1>
            </div>
            <div class="search-container mr-5">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Cari anggota..."
                    class="w-full px-4 py-2 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-300"
                >
                <div id="searchResults" class="search-results text-black">
                </div>
            </div>
        </div>
    </header>

    <div class="tree-container" id="treeContainer">
        <div class="tree-wrapper" id="treeWrapper">
            <main class="tree">
                <ul>
                    <?php
                    while ($root = $result->fetch_assoc()) {
                        echo renderFamilyTree($conn, $root);
                    }
                    ?>
                </ul>
            </main>
        </div>
    </div>

    <div class="zoom-controls">
        <button class="zoom-button" onclick="zoomIn()">+</button>
        <div class="zoom-level" id="zoomLevel">100%</div>
        <button class="zoom-button" onclick="zoomOut()">-</button>
    </div>

    <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script>
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        const membersList = <?php echo json_encode($membersList); ?>;
        
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            if (searchTerm.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            const filteredMembers = membersList.filter(member => 
                member.nama.toLowerCase().includes(searchTerm)
            );

            displaySearchResults(filteredMembers);
        });

        function displaySearchResults(results) {
            searchResults.innerHTML = '';
            
            if (results.length === 0) {
                searchResults.innerHTML = '<div class="search-result-item">Tidak ada hasil</div>';
                searchResults.style.display = 'block';
                return;
            }

            results.forEach(member => {
                const div = document.createElement('div');
                div.className = 'search-result-item';
                div.textContent = `${member.nama} (Sundut ${member.generasi})`;
                div.addEventListener('click', () => scrollToMember(member.nama));
                searchResults.appendChild(div);
            });

            searchResults.style.display = 'block';
        }

        function scrollToMember(memberName) {
            const existingHighlights = document.querySelectorAll('.highlight');
            existingHighlights.forEach(el => el.classList.remove('highlight'));

            const memberCards = document.querySelectorAll('.tree-card');
            let targetCard = null;

            memberCards.forEach(card => {
                const nameElement = card.querySelector('h3');
                if (nameElement && nameElement.textContent === memberName) {
                    targetCard = card;
                }
            });

            if (targetCard) {
                const treeWrapper = document.getElementById('treeWrapper');
                const treeContainer = document.getElementById('treeContainer');
                const containerRect = treeContainer.getBoundingClientRect();
                const cardRect = targetCard.getBoundingClientRect();

                targetCard.classList.add('highlight');

                const newX = -cardRect.left + containerRect.width/2 - cardRect.width/2;
                const newY = -cardRect.top + containerRect.height/2 - cardRect.height/2;

                currentPoint.x = newX;
                currentPoint.y = newY;
                updateTreePosition();

                searchResults.style.display = 'none';
                searchInput.value = '';

                setTimeout(() => {
                    targetCard.classList.remove('highlight');
                }, 2000);
            }
        }

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        function toggleFooter() {
            const footerStatic = document.getElementById('footer-static');
            const footerFixed = document.getElementById('footer-fixed');
            const isScrollable = document.body.scrollHeight > window.innerHeight;
            
            if (isScrollable) {
                footerStatic.classList.remove('hidden');
                footerFixed.classList.add('hidden');
            } else {
                footerStatic.classList.add('hidden');
                footerFixed.classList.remove('hidden');
            }
        }

        window.addEventListener('load', toggleFooter);
        window.addEventListener('resize', toggleFooter);

        let scale = 1;
        let panning = false;
        let startPoint = { x: 0, y: 0 };
        let currentPoint = { x: 0, y: 0 };
        const treeWrapper = document.getElementById('treeWrapper');
        const treeContainer = document.getElementById('treeContainer');
        const zoomLevelDisplay = document.getElementById('zoomLevel');
        const MIN_SCALE = 0.1;
        const MAX_SCALE = 2;
        const ZOOM_SPEED = 0.02;

        let initialPinchDistance = null;
        let initialScale = null;
        let lastZoomCenter = null;

        function centerTree() {
            const containerRect = treeContainer.getBoundingClientRect();
            const treeRect = treeWrapper.getBoundingClientRect();
            
            currentPoint.x = (containerRect.width - treeRect.width) / 2;
            currentPoint.y = 0;
            
            updateTreePosition();
        }

        function zoom(direction, point = null) {
            const oldScale = scale;
            
            if (direction === 'in' && scale < MAX_SCALE) {
                scale += ZOOM_SPEED;
            } else if (direction === 'out' && scale > MIN_SCALE) {
                scale -= ZOOM_SPEED;
            } else {
                return;
            }

            if (point) {
                const scaleRatio = scale / oldScale;
                const containerRect = treeContainer.getBoundingClientRect();
                const dx = (point.x - containerRect.left - currentPoint.x) * (scaleRatio - 1);
                const dy = (point.y - containerRect.top - currentPoint.y) * (scaleRatio - 1);
                
                currentPoint.x -= dx;
                currentPoint.y -= dy;
            }

            updateTreePosition();
            updateZoomLevel();
        }

        function zoomIn() {
            zoom('in', lastZoomCenter);
        }

        function zoomOut() {
            zoom('out', lastZoomCenter);
        }

        function updateZoomLevel() {
            zoomLevelDisplay.textContent = `${Math.round(scale * 100)}%`;
        }

        function getTouchDistance(touch1, touch2) {
            return Math.hypot(
                touch2.clientX - touch1.clientX,
                touch2.clientY - touch1.clientY
            );
        }

        function getTouchCenter(touch1, touch2) {
            return {
                x: (touch1.clientX + touch2.clientX) / 2,
                y: (touch1.clientY + touch2.clientY) / 2
            };
        }

        treeContainer.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                e.preventDefault();
                initialPinchDistance = getTouchDistance(e.touches[0], e.touches[1]);
                initialScale = scale;
                lastZoomCenter = getTouchCenter(e.touches[0], e.touches[1]);
            } else if (e.touches.length === 1) {
                panning = true;
                startPoint = {
                    x: e.touches[0].clientX - currentPoint.x,
                    y: e.touches[0].clientY - currentPoint.y
                };
            }
        }, { passive: false });

        treeContainer.addEventListener('touchmove', function(e) {
            if (e.touches.length === 2) {
                e.preventDefault();
                const currentDistance = getTouchDistance(e.touches[0], e.touches[1]);
                const currentCenter = getTouchCenter(e.touches[0], e.touches[1]);
                
                if (initialPinchDistance !== null) {
                    const oldScale = scale;
                    const pinchScale = currentDistance / initialPinchDistance;
                    scale = Math.min(Math.max(initialScale * pinchScale, MIN_SCALE), MAX_SCALE);

                    const scaleRatio = scale / oldScale;
                    const containerRect = treeContainer.getBoundingClientRect();
                    const dx = (currentCenter.x - containerRect.left - currentPoint.x) * (scaleRatio - 1);
                    const dy = (currentCenter.y - containerRect.top - currentPoint.y) * (scaleRatio - 1);
                    
                    currentPoint.x -= dx;
                    currentPoint.y -= dy;
                    
                    lastZoomCenter = currentCenter;
                    updateTreePosition();
                    updateZoomLevel();
                }
            } else if (e.touches.length === 1 && panning) {
                currentPoint = {
                    x: e.touches[0].clientX - startPoint.x,
                    y: e.touches[0].clientY - startPoint.y
                };
                updateTreePosition();
            }
        }, { passive: false });

        treeContainer.addEventListener('touchend', function(e) {
            initialPinchDistance = null;
            initialScale = null;
            if (e.touches.length === 0) {
                panning = false;
            }
        });

        treeContainer.addEventListener('wheel', function(e) {
            e.preventDefault();
            const rect = treeContainer.getBoundingClientRect();
            const point = {
                x: e.clientX,
                y: e.clientY
            };
            
            if (e.deltaY < 0) {
                zoom('in', point);
            } else {
                zoom('out', point);
            }
        });

        treeContainer.addEventListener('mousedown', function(e) {
            panning = true;
            startPoint = { x: e.clientX - currentPoint.x, y: e.clientY - currentPoint.y };
        });

        document.addEventListener('mousemove', function(e) {
            if (panning) {
                currentPoint = {
                    x: e.clientX - startPoint.x,
                    y: e.clientY - startPoint.y
                };
                updateTreePosition();
            }
        });

        document.addEventListener('mouseup', function() {
            panning = false;
        });

        function updateTreePosition() {
            treeWrapper.style.transform = `translate(${currentPoint.x}px, ${currentPoint.y}px) scale(${scale})`;
        }

        window.addEventListener('load', centerTree);
        window.addEventListener('resize', centerTree);
    </script>
</body>
</html>
<?php
session_start();
require_once '../../server/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk mengambil semua anggota keluarga
$query = "
    SELECT a.*,
           ayah.nama as nama_ayah,
           ibu.nama as nama_ibu,
           istri1.nama as nama_istri_1,
           istri2.nama as nama_istri_2,
           istri3.nama as nama_istri_3,
           (SELECT GROUP_CONCAT(DISTINCT suami.nama ORDER BY suami.nama ASC SEPARATOR ', ')
            FROM anggota suami 
            WHERE (suami.id_istri_1 = a.id 
               OR suami.id_istri_2 = a.id 
               OR suami.id_istri_3 = a.id)) as nama_suami,
           GROUP_CONCAT(DISTINCT anak.nama ORDER BY anak.id ASC SEPARATOR ', ') as nama_anak
    FROM anggota a
    LEFT JOIN anggota ayah ON a.id_ayah = ayah.id
    LEFT JOIN anggota ibu ON a.id_ibu = ibu.id
    LEFT JOIN anggota istri1 ON a.id_istri_1 = istri1.id
    LEFT JOIN anggota istri2 ON a.id_istri_2 = istri2.id
    LEFT JOIN anggota istri3 ON a.id_istri_3 = istri3.id
    LEFT JOIN anggota anak ON anak.id_ayah = a.id OR anak.id_ibu = a.id
    GROUP BY a.id
    ORDER BY a.generasi, a.id
";

$result = $conn->query($query);

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisasi Pohon Keluarga - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.23.0/cytoscape.min.js"></script>
    <style>
        #cy {
            width: 100%;
            height: 600px;
            border: 1px solid #ccc;
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="../../index.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded"><</a>
                <h1 class="text-xl font-semibold ml-5">Visualisasi Pohon Keluarga</h1>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
        <div id="cy"></div>
    </main>

    <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-auto">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>

    <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script>
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

        document.addEventListener('DOMContentLoaded', function() {
            const members = <?php echo json_encode($members); ?>;

            const elements = [];
            const memberMap = {};

            // Membuat node untuk setiap anggota keluarga
            members.forEach(member => {
                const memberId = member.id;
                const memberName = member.nama;
                const memberGeneration = member.generasi;
                const memberImage = member.foto ? `../../${member.foto}` : 
                    (member.jenis_kelamin === 'Laki-laki' ? '../../assets/img/default_male.jpg' : '../../assets/img/default_female.jpg');

                memberMap[memberId] = memberName;

                elements.push({
                    data: {
                        id: memberId,
                        name: memberName,
                        image: memberImage,
                        generation: `Sundut ${memberGeneration}`
                    }
                });

                // Menambahkan edge untuk hubungan ayah
                if (member.id_ayah) {
                    elements.push({
                        data: {
                            id: `edge-${member.id_ayah}-${memberId}`,
                            source: member.id_ayah,
                            target: memberId
                        }
                    });
                }

                // Menambahkan edge untuk hubungan ibu
                if (member.id_ibu) {
                    elements.push({
                        data: {
                            id: `edge-${member.id_ibu}-${memberId}`,
                            source: member.id_ibu,
                            target: memberId
                        }
                    });
                }
            });

            const cy = cytoscape({
                container: document.getElementById('cy'),
                elements: elements,
                style: [
                    {
                        selector: 'node',
                        style: {
                            'label': 'data(name)',
                            'text-valign': 'bottom',
                            'text-halign': 'center',
                            'background-image': 'data(image)',
                            'background-fit': 'cover',
                            'width': 80,
                            'height': 80,
                            'border-width': 2,
                            'border-color': '#4a5568'
                        }
                    },
                    {
                        selector: 'edge',
                        style: {
                            'width': 2,
                            'line-color': '#4a5568',
                            'curve-style': 'bezier'
                        }
                    }
                ],
                layout: {
                    name: 'breadthfirst',
                    directed: true,
                    padding: 10,
                    spacingFactor: 1.5,
                    animate: true
                }
            });

            cy.on('tap', 'node', function(evt) {
                const node = evt.target;
                const name = node.data('name');
                const generation = node.data('generation');
                alert(`Nama: ${name}\nSundut: ${generation}`);
            });
        });
    </script>
</body>
</html>
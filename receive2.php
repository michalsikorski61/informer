<?php
header("Content-Type: text/html");

$filePath = "computer_info.json";

// Odbieranie i zapisywanie danych
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim(file_get_contents("php://input"));
    $decodedContent = json_decode($content, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $existingData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
        $existingData[] = $decodedContent;
        file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));
    }
}

echo '<html><head><meta http-equiv="refresh" content="200"><style>.high-usage { color: red; }</style><script>
function toggleDetails(id) {
    var x = document.getElementById(id);
    if (x.style.display === "none") {
        x.style.display = "table-row";
    } else {
        x.style.display = "none";
    }
}
</script></head><body>';
echo '<input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Wyszukaj po nazwie komputera...">
<button onclick="sortTable(0)">Sortuj po Timestamp</button>
<button onclick="sortTable(1)">Sortuj po Computer Name</button>
';
if (file_exists($filePath) && filesize($filePath) > 0) {
    $data = json_decode(file_get_contents($filePath), true);
    echo "<h1>Dashboard</h1>";
    echo "<table border='1' id='myTable'>";
    echo "<tr><th>Timestamp</th><th>Computer Name</th><th>Details</th></tr>";

    foreach ($data as $index => $record) {
        $detailsId = "details-" . $index;
        echo "<tr onclick='toggleDetails(\"$detailsId\")'>";
    
        // Użyj wartości 'Time' jako 'timestamp'
        $time = isset($record['Time']) ? htmlspecialchars($record['Time']) : 'N/A';
        echo "<td>" . $time . "</td>";
    
        // Reszta kodu...
        $computerName = isset($record['ComputerName']) ? htmlspecialchars($record['ComputerName']) : 'N/A';
        echo "<td>" . $computerName . "</td>";
    
        echo "<td>Click to toggle details</td>";
        echo "</tr>";
        echo "<tr id='$detailsId' style='display:none;'><td colspan='3'><div class='details-container'>";
        
        // Funkcja displayRecordDetails...
        displayRecordDetails($record);
        
        echo "</div></td></tr>";
    }
    echo "</table>";
} else {
    echo "No data available";
}

echo '</body></html>';
function displayRecordDetails($record) {
    foreach ($record as $key => $value) {
        if ($key != 'timestamp' && $key != 'ComputerName') {
            echo "<div><strong>" . htmlspecialchars($key) . ":</strong> ";
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        echo "<div><strong>" . htmlspecialchars($subKey) . ":</strong> ";
                        echo "<pre>" . htmlspecialchars(json_encode($subValue, JSON_PRETTY_PRINT)) . "</pre></div>";
                    } else {
                        echo htmlspecialchars($subKey . ': ' . formatValue($subValue)) . "<br>";
                    }
                }
            } else {
                echo htmlspecialchars(formatValue($value));
            }
            echo "</div>";
        }
    }
}

function formatValue($value) {
    // Dla procentów
    if (is_numeric($value)) {
        return $value . "%";
    }
    // Dla innych wartości
    return $value;
}
echo '<script>
function searchTable() {
    var input, filter, table, tr, td, i, j, txtValue, found;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("myTable");
    tr = table.getElementsByTagName("tr");

    for (i = 1; i < tr.length; i++) { // Start od 1, aby pominąć nagłówek tabeli
        tr[i].style.display = "none";
        td = tr[i].getElementsByTagName("td");
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    break; // Znaleziono dopasowanie, przerywa wewnętrzną pętlę
                }
            }
        }
    }
}

function searchTable() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("myTable");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[1]; // Wyszukaj w kolumnie Computer Name
        if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }       
    }
}
</script>
';
?>

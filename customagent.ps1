# Ustawienie zasad wykonania skryptów na 'RemoteSigned'
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser -Force

# Zbieranie podstawowych informacji o systemie
$computerInfo = @{
    ComputerName = $env:COMPUTERNAME
    OSVersion = [System.Environment]::OSVersion.VersionString
    User = [System.Environment]::UserName
    Time = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    CPUUsage = Get-WmiObject win32_processor | Measure-Object -Property LoadPercentage -Average | Select-Object -ExpandProperty Average
    RAMUsage = Get-WmiObject win32_operatingsystem | Select-Object @{Name="RAMUsage";Expression={"{0:N2}" -f ((($_.TotalVisibleMemorySize - $_.FreePhysicalMemory)*100)/ $_.TotalVisibleMemorySize)}}
    DiskUsage = Get-WmiObject Win32_LogicalDisk -Filter "DeviceID = 'C:'" | Select-Object @{Name="DiskUsage";Expression={"{0:N2}" -f (($_.Size - $_.FreeSpace) / $_.Size * 100)}}
}

# Zbieranie danych o konfiguracji sieciowej
$networkAdapters = Get-NetAdapter | Where-Object { $_.Status -eq "Up" } | Select-Object -Property Name, InterfaceDescription, MacAddress
$ipAddresses = Get-NetIPAddress | Where-Object { $_.AddressFamily -eq "IPv4" -and $_.PrefixOrigin -ne "WellKnown" } | Select-Object -Property IPAddress, InterfaceAlias
$dnsServers = Get-DnsClientServerAddress | Where-Object { $_.AddressFamily -eq 2 } | Select-Object -Property ServerAddresses, InterfaceAlias

$computerInfo['NetworkAdapters'] = $networkAdapters
$computerInfo['IPAddresses'] = $ipAddresses
$computerInfo['DnsServers'] = $dnsServers

# Konwersja danych do formatu JSON
$jsonData = $computerInfo | ConvertTo-Json -Depth 5 -Compress

# Definiowanie parametrów żądania
$params = @{
    Method = 'POST'
    Uri = 'http://sbdev01.supra1.local/poligon/wordpress/receive2.php'
    ContentType = 'application/json'
    Body = $jsonData
}

# Wysyłanie danych do serwera
$response = Invoke-RestMethod @params
Write-Output "Odpowiedź serwera: $response"

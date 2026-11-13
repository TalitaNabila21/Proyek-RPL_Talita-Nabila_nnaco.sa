# pastel_images.ps1
Add-Type -AssemblyName System.Drawing

$Products = @(
    @{ Id=4; Name="Karakter Lucu"; Slug="gantungan-kunci-karakter-lucu"; Color="#ffb6c1" },
    @{ Id=7; Name="Boneka Mini"; Slug="gantungan-kunci-boneka-mini"; Color="#ffb6c1" },
    @{ Id=12; Name="Penghapus Karakter"; Slug="penghapus-karakter"; Color="#b0e0e6" },
    @{ Id=16; Name="Tempat Pensil"; Slug="tempat-pensil-handmade"; Color="#b0e0e6" },
    @{ Id=24; Name="Skincare Eksklusif"; Slug="skincare-eksklusif"; Color="#dda0dd" },
    @{ Id=27; Name="Kerudung Eksklusif"; Slug="kerudung-eksklusif"; Color="#ffe4c4" },
    @{ Id=29; Name="Masker Eksklusif"; Slug="masker-eksklusif"; Color="#98fb98" },
    @{ Id=32; Name="Mainan Eksklusif"; Slug="mainan-eksklusif"; Color="#ffb6c1" }
)

$OutputDir = "C:\xampp\htdocs\nnaco_sa\public\assets\images\products"

foreach ($p in $Products) {
    $bmp = New-Object System.Drawing.Bitmap(600, 600)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    
    $bgBrush = New-Object System.Drawing.SolidBrush([System.Drawing.ColorTranslator]::FromHtml($p.Color))
    $g.FillRectangle($bgBrush, 0, 0, 600, 600)
    
    $font = New-Object System.Drawing.Font("Arial", 40, [System.Drawing.FontStyle]::Bold)
    $textBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(80, 80, 80))
    $shadowBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
    
    $format = New-Object System.Drawing.StringFormat
    $format.Alignment = [System.Drawing.StringAlignment]::Center
    $format.LineAlignment = [System.Drawing.StringAlignment]::Center
    
    $rect = New-Object System.Drawing.RectangleF(0, 0, 600, 600)
    $rectShadow = New-Object System.Drawing.RectangleF(2, 2, 600, 600)
    
    $g.DrawString($p.Name, $font, $shadowBrush, $rectShadow, $format)
    $g.DrawString($p.Name, $font, $textBrush, $rect, $format)
    
    $idStr = "{0:D3}" -f $p.Id
    $filename = "$($p.Slug)-$idStr.jpg"
    $filepath = Join-Path $OutputDir $filename
    
    $bmp.Save($filepath, [System.Drawing.Imaging.ImageFormat]::Jpeg)
    
    $g.Dispose()
    $bmp.Dispose()
    $bgBrush.Dispose()
    $textBrush.Dispose()
    $shadowBrush.Dispose()
    $font.Dispose()
    
    Write-Host "Created $filename"
}

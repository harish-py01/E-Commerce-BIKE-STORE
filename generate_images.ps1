Add-Type -AssemblyName System.Drawing

$directories = @(
    ".\uploads"
)

$items = @(
    "brake_pad.jpg", "disc_rotor.jpg", "spark_plug.jpg", "air_filter.jpg",
    "chain_kit.jpg", "tires.jpg", "engine_oil.jpg", "exhaust.jpg", "helmet.jpg",
    "oil_filter.jpg", "cat_braking.jpg", "cat_engine.jpg", "cat_chains.jpg",
    "cat_tires.jpg", "cat_oils.jpg", "cat_accessories.jpg", "brand_brembo.jpg",
    "brand_yamaha.jpg", "brand_honda.jpg", "brand_akrapovic.jpg", "brand_motul.jpg",
    "brand_dunlop.jpg", "default_product.jpg", "default_cat.jpg", "default_brand.jpg"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }

    foreach ($item in $items) {
        $filePath = Join-Path $dir $item
        $bmp = New-Object System.Drawing.Bitmap 600, 400
        $g = [System.Drawing.Graphics]::FromImage($bmp)
        $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias

        # Fill background with light sleek grey
        $g.Clear([System.Drawing.Color]::FromArgb(248, 249, 250))

        # Outer Accent Border
        $borderPen = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(220, 53, 69), 6)
        $g.DrawRectangle($borderPen, 8, 8, 584, 384)

        # Inner Card Shape
        $cardBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(255, 255, 255))
        $g.FillRectangle($cardBrush, 20, 20, 560, 360)

        # Draw Decorative Icon/Pill Banner
        $pillBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(220, 53, 69))
        $g.FillRectangle($pillBrush, 200, 40, 200, 35)

        $fontBanner = New-Object System.Drawing.Font("Segoe UI", 11, [System.Drawing.FontStyle]::Bold)
        $brushBanner = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
        $sfCenter = New-Object System.Drawing.StringFormat
        $sfCenter.Alignment = [System.Drawing.StringAlignment]::Center
        $sfCenter.LineAlignment = [System.Drawing.StringAlignment]::Center

        $bannerRect = New-Object System.Drawing.RectangleF 200, 40, 200, 35
        $g.DrawString("BIKE SPARE PART", $fontBanner, $brushBanner, $bannerRect, $sfCenter)

        # Main Title Text
        $title = $item.Replace(".jpg", "").Replace("_", " ").ToUpper()
        $fontTitle = New-Object System.Drawing.Font("Segoe UI", 20, [System.Drawing.FontStyle]::Bold)
        $brushTitle = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(33, 37, 41))

        $titleRect = New-Object System.Drawing.RectangleF 30, 120, 540, 140
        $g.DrawString($title, $fontTitle, $brushTitle, $titleRect, $sfCenter)

        # Subtitle Text
        $fontSub = New-Object System.Drawing.Font("Segoe UI", 12, [System.Drawing.FontStyle]::Italic)
        $brushSub = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(108, 117, 125))
        $subRect = New-Object System.Drawing.RectangleF 30, 280, 540, 60
        $g.DrawString("Genuine Quality Guaranteed", $fontSub, $brushSub, $subRect, $sfCenter)

        # Save as genuine Binary JPEG format
        $bmp.Save($filePath, [System.Drawing.Imaging.ImageFormat]::Jpeg)

        $g.Dispose()
        $bmp.Dispose()
    }
}

Write-Host "Successfully generated valid JPEG images for all spare parts."

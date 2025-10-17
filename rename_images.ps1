# Script to rename long image filenames to shorter ones
# Run this script to rename all long image files

$renameMap = @{
    "Chuyên gia năng lượng mặt trời đang giới thiệu mô hình giải pháp năng lượng cho một gia đình Việt Nam tại phòng khách ấm cúng của họ.png" = "consultant-family-1.png"
    "Chuyên gia năng lượng mặt trời đang giới thiệu mô hình giải pháp năng lượng cho một gia đình Việt Nam tại phòng khách ấm cúng.png" = "consultant-family-2.png"
    "Đây là hình ảnh một kỹ sư đang lắp đặt tấm pin năng lượng mặt trời.png" = "engineer-installing-solar.png"
    "Đây là một hình ảnh về một ngôi nhà ở châu Á được lắp đặt tấm pin năng lượng mặt trời.png" = "asian-house-solar.png"
    "Kỹ sư đang kiểm tra cẩn thận các tấm pin năng lượng mặt trời trên mái nhà, với khung cảnh thành phố Việt Nam phía xa. Bầu trời trong xanh và ánh nắng rực rỡ, thể hiện hiệu quả của hệ thống.png" = "engineer-checking-city-view.png"
    "Kỹ sư đang kiểm tra hệ thống pin năng lượng mặt trời trên mái nhà dưới ánh nắng chan hòa.png" = "engineer-checking-sunshine.png"
    "Một góc nhìn rộng hơn của mái nhà Việt Nam với các tấm pin năng lượng mặt trời được lắp đặt hoàn chỉnh. Ngôi nhà nằm trong khu dân cư xanh mát, thể hiện sự hòa hợp giữa công nghệ và thiên nhiên.png" = "vn-roof-wide-view.png"
    "Nhân viên tư vấn của bạn đang giới thiệu về các giải pháp năng lượng mặt trời cho một cặp vợ chồng tại nhà của họ ở Việt Nam, trên mái nhà có thể thấy một phần tấm pin đã được lắp đặt.png" = "consultant-couple-home.png"
    "Nhân viên tư vấn đang trao đổi với khách hàng về lợi ích của năng lượng mặt trời tại nhà của họ ở Việt Nam.png" = "consultant-customer-benefits.png"
}

Write-Host "Starting to rename image files..."

foreach ($oldName in $renameMap.Keys) {
    $newName = $renameMap[$oldName]
    $oldPath = "Photo\$oldName"
    $newPath = "Photo\$newName"
    
    if (Test-Path $oldPath) {
        try {
            Rename-Item -Path $oldPath -NewName $newName -Force
            Write-Host "✓ Renamed: $oldName -> $newName"
        }
        catch {
            Write-Host "✗ Failed to rename: $oldName - Error: $($_.Exception.Message)"
        }
    }
    else {
        Write-Host "⚠ File not found: $oldName"
    }
}

Write-Host "Renaming completed!"

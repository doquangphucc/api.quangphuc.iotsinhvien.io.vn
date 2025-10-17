# PowerShell script to update auth-user-name CSS in all HTML files
$files = @(
    "html/gioi-thieu.html",
    "html/tin-tuc.html", 
    "html/lien-he.html",
    "html/du-an.html",
    "html/khao-sat-dien-mat-troi.html",
    "html/survey_history.html",
    "html/order_history.html",
    "html/dat-hang.html",
    "html/gio-hang.html",
    "html/user_profile.html",
    "html/register.html"
)

$oldCSS = @"
        .auth-user-name {
            font-weight: 600;
            padding: 0.6rem 0.25rem;
            white-space: nowrap;
            text-decoration: none;
        }
"@

$newCSS = @"
        .auth-user-name {
            font-weight: 600;
            font-size: 0.875rem; /* 14px - smaller font */
            padding: 0.5rem 0.75rem; /* Reduced padding */
            white-space: nowrap;
            text-decoration: none;
            max-width: 120px; /* Limit width */
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
"@

$oldLogoutCSS = @"
        .auth-user-name + .auth-btn--register { 
             background-color: #fee2e2;
             color: #b91c1c;
        }
"@

$newLogoutCSS = @"
        .auth-user-name + .auth-btn--register { 
             background-color: #fee2e2;
             color: #b91c1c;
             font-size: 0.75rem; /* 12px - smaller font */
             padding: 0.5rem 0.75rem; /* Reduced padding */
             min-width: auto; /* Remove minimum width */
        }
"@

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "Updating $file..."
        $content = Get-Content $file -Raw -Encoding UTF8
        $content = $content -replace [regex]::Escape($oldCSS), $newCSS
        $content = $content -replace [regex]::Escape($oldLogoutCSS), $newLogoutCSS
        Set-Content $file -Value $content -Encoding UTF8
        Write-Host "Updated $file successfully"
    } else {
        Write-Host "File $file not found"
    }
}

Write-Host "All files updated!"

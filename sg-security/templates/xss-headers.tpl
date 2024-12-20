# SGS XSS Header Service
<IfModule mod_headers.c>
    # SGS XSS
     Header always set X-Content-Type-Options "nosniff"
     Header set X-XSS-Protection "1; mode=block"
    # SGS XSS END
</IfModule>
# SGS XSS Header Service END

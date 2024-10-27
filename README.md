# PHP Sitemap & robots.txt Generator

A powerful PHP script that automatically generates SEO-friendly sitemap.xml and robots.txt files by scanning your website's directory structure. Features an intuitive admin interface for customizing which pages to include or exclude.

## 🌟 Key Features

### Directory Scanning
- Recursively scans your `public_html` folder
- Automatically excludes common non-web files (php, txt, images, etc.)
- Creates organized tree structure of files and directories

### Admin Interface
- User-friendly checkboxes for each file and directory
- Granular control with include/exclude options
- Convenient Select All/Deselect All functionality
- Session-based settings persistence

### Sitemap Generation
- Standards-compliant XML sitemap creation
- Automatic `lastmod` dates based on file modifications
- Intelligent priority assignments:
  - Homepage: 1.0
  - Other pages: 0.8
- URL encoding for spaces and special characters

### robots.txt Generation
- Automatic robots.txt file creation
- Includes sitemap location reference
- Configurable Disallow rules for excluded paths
- Default Allow rules for included content

### SEO Optimizations
- Search engine optimized XML formatting
- Smart priority weighting system
- Fresh content indicators with last modified dates
- Homepage prioritization
- Proper URL encoding for all entries

## 📋 Installation & Usage

1. Create a new file (e.g., `sitemap-admin.php`) and paste the script code
2. Set up authentication:
   - Create a `login.php` file (not included)
   - Implement your preferred authentication method
3. Access the admin interface through your browser
4. Select/deselect files and folders as needed
5. Click "Generate Sitemap & robots.txt"

## 🚀 SEO Best Practices

### Submit Your Sitemap
1. Create a Google Search Console account
2. Add and verify your website ownership
3. Submit your sitemap URL: `yourdomain.com/sitemap.xml`

### Optimize Your Homepage
Add these meta tags to your static homepage:
```html
<meta name="description" content="Your site description">
<meta name="keywords" content="relevant, keywords, here">
<meta name="robots" content="index, follow">
```

## 💡 Advanced SEO Recommendations

Consider implementing these additional SEO enhancements:
- Schema.org markup for rich search results
- Canonical URLs for duplicate content management
- XML sitemap index for large-scale websites
- Mobile-responsive design optimization

## 📝 Notes

- Ensure proper file permissions for writing sitemap.xml and robots.txt
- Regularly update your sitemap as your content changes
- Monitor Google Search Console for crawl errors
- Test your sitemap validity using online tools

## 🔒 Security

- Always implement proper authentication for the admin interface
- Restrict access to sensitive directories
- Regularly update your PHP version
- Monitor access logs for suspicious activity

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

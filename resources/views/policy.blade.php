<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    <style>
        :root {
            --primary-color: #7C4DFF;
            --text-color: #374151;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --highlight-color: #fef3c7;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .effective-date {
            color: #6b7280;
            font-size: 0.9em;
        }

        .summary-box {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
        }

        .summary-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .toc-container {
            background-color: var(--light-bg);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .toc-title {
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toc-title::before {
            content: "📋";
        }

        .toc-list {
            padding-left: 20px;
            columns: 2;
            column-gap: 30px;
        }

        .toc-list li {
            margin-bottom: 8px;
            break-inside: avoid;
        }

        .toc-list a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.2s;
        }

        .toc-list a:hover {
            text-decoration: underline;
            color: #3730a3;
        }

        section {
            margin-bottom: 30px;
        }

        h2 {
            color: var(--primary-color);
            margin-top: 40px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }

        h3 {
            margin-top: 25px;
            color: #111827;
            font-weight: 500;
        }

        p {
            margin-bottom: 15px;
        }

        .highlight {
            background-color: var(--highlight-color);
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 500;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 0.9em;
        }

        .data-table th {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            padding: 12px 15px;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tr:nth-child(even) {
            background-color: var(--light-bg);
        }

        .contact-card {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .update-notice {
            background-color: #ecfdf5;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #10b981;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .toc-list {
                columns: 1;
            }

            h1 {
                font-size: 1.6em;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Privacy Policy</h1>
        <div class="effective-date">Effective Date: June 15, 2023</div>
    </header>

    <div class="summary-box">
        <div class="summary-title">Policy Summary</div>
        <p>This privacy policy explains how we collect, use, and protect your personal information when you use our services. We are committed to protecting your privacy and handling your data transparently and securely.</p>
    </div>

    <div class="toc-container">
        <div class="toc-title">Table of Contents</div>
        <ol class="toc-list">
            <li><a href="#information-we-collect">Information We Collect</a></li>
            <li><a href="#how-we-use-info">How We Use Information</a></li>
            <li><a href="#cookies">Cookies and Tracking</a></li>
            <li><a href="#data-sharing">Data Sharing</a></li>
            <li><a href="#data-security">Data Security</a></li>
            <li><a href="#your-rights">Your Rights</a></li>
            <li><a href="#children">Children's Privacy</a></li>
            <li><a href="#changes">Policy Changes</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ol>
    </div>

    <section id="information-we-collect">
        <h2>1. Information We Collect</h2>
        <p>We collect several types of information from and about users of our services:</p>

        <h3>Personal Information</h3>
        <p>This may include:</p>
        <ul>
            <li>Name and contact details (email, phone number, address)</li>
            <li>Account credentials (username and password)</li>
            <li>Payment information (for paid services)</li>
            <li>Demographic information</li>
        </ul>

        <h3>Usage Data</h3>
        <p>We automatically collect information about how you interact with our services:</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data Type</th>
                    <th>Examples</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Device Information</td>
                    <td>IP address, browser type, device type</td>
                </tr>
                <tr>
                    <td>Usage Information</td>
                    <td>Pages visited, time spent, clickstream data</td>
                </tr>
                <tr>
                    <td>Location Data</td>
                    <td>General location (city/country level)</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section id="how-we-use-info">
        <h2>2. How We Use Information</h2>
        <p>We use the information we collect for the following purposes:</p>

        <ul>
            <li>To provide and maintain our services</li>
            <li>To improve and personalize user experience</li>
            <li>To process transactions and send service-related communications</li>
            <li>For security and fraud prevention</li>
            <li>To comply with legal obligations</li>
        </ul>

        <p class="highlight">We will never sell your personal information to third parties.</p>
    </section>

    <section id="cookies">
        <h2>3. Cookies and Tracking Technologies</h2>
        <p>We use cookies and similar tracking technologies to track activity on our service.</p>

        <h3>Types of Cookies</h3>
        <ul>
            <li><strong>Essential Cookies:</strong> Necessary for the website to function</li>
            <li><strong>Performance Cookies:</strong> Help us understand how visitors interact</li>
            <li><strong>Functional Cookies:</strong> Enable enhanced functionality</li>
            <li><strong>Marketing Cookies:</strong> Used for advertising purposes</li>
        </ul>

        <p>You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>
    </section>

    <!-- Additional sections would continue here -->

    <section id="contact">
        <h2>9. Contact Us</h2>
        <p>If you have any questions about this privacy policy or our data practices:</p>

        <div class="contact-card">
            <p><strong>Data Protection Officer</strong><br>
            Email: privacy@example.com<br>
            Phone: +1 (555) 123-4567<br>
            Address: 123 Privacy Lane, Data City, DC 12345</p>

            <p>For EU residents, you may contact our EU representative:<br>
            EU-Rep GmbH<br>
            Privacy Avenue 1<br>
            10115 Berlin, Germany</p>
        </div>
    </section>

    <div class="update-notice">
        <h3>Updates to This Policy</h3>
        <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Effective Date" at the top.</p>
        <p>You are advised to review this Privacy Policy periodically for any changes.</p>
    </div>
</body>
</html>

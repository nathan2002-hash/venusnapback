<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="icon">
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="apple-touch-icon">
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
        <img src="{{ asset('assets1/img/logo1.png') }}" width="50" alt="">
        <h1>Venusnap Privacy Policy</h1>
        <div class="effective-date">Effective Date: June 15, 2025</div>
    </header>

    <div class="summary-box">
        <div class="summary-title">Policy Summary</div>
        <p>Venusnap respects your privacy and is committed to protecting the personal information you share with us. This Privacy Policy outlines how we collect, use, disclose, and safeguard your information when you use our platform and services, in accordance with the <b>Data Protection Act No. 3 of 2021 of Zambia</b>.</p>
    </div>

    <div class="toc-container">
        <div class="toc-title">Table of Contents</div>
        <ol class="toc-list">
            <li><a href="#information-we-collect">Information We Collect</a></li>
            <li><a href="#how-we-use-info">How We Use Information</a></li>
            <li><a href="#legal-basis">Legal Basis for Processing</a></li>
            <li><a href="#data-sharing">Data Sharing</a></li>
            <li><a href="#data-storage">Data Storage and Security</a></li>
            <li><a href="#your-rights">Your Rights</a></li>
            <li><a href="#childrens">Children's Privacy</a></li>
            <li><a href="#changes">Policy Changes</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ol>
    </div>

    <section id="information-we-collect">
        <h2>1. Information We Collect</h2>
        <p>We collect the following types of personal data when you use Venusnap:</p>

        <h3>Personal Information</h3>
        <p>This may include:</p>
        <ul>
            <li>Account Information: Name, username, email address, phone number, date of birth, country, password.</li>
            <li>Photo and Media Content: Images, captions, and metadata associated with uploads.</li>
            <li>Payment information (for payout services)</li>
            <li>Usage Information: IP address, browser type, device information, and how you use our platform.</li>
            <li>Location Data: If you enable location sharing, we may collect GPS data or approximate location based on IP.</li>
            <li>Communications: Any correspondence with our support team or other users through the platform.</li>
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
            <li>To create and manage your account</li>
            <li>To provide and maintain our services</li>
            <li>To personalize your user experience</li>
            <li>To process transactions and send service-related communications</li>
            <li>To ensure the safety and security of our platform</li>
            <li>To comply with legal obligations under Zambian law</li>
            <li>To improve our services and develop new features.</li>
        </ul>

        <p class="highlight">We will never sell your personal information to third parties.</p>
    </section>

     <section id="legal-basis">
        <h2>3. Legal Basis for Processing</h2>
        <p class="highlight">We will never sell your personal information to third parties.</p>

        <ul>
            <li>Your consent.</li>
            <li>Fulfillment of a contract (e.g., account registration).</li>
            <li>Legitimate interests (e.g., improving the platform, preventing fraud).</li>
            <li>Compliance with legal obligations (e.g., responding to lawful requests).</li>
        </ul>
    </section>

     <section id="sharing-disclosure">
        <h2>4. Sharing and Disclosure</h2>
        <p>We do not sell or rent your personal information. We may share your information:</p>

        <ul>
            <li>With service providers who assist us in operating Venusnap.</li>
            <li>When required by law, court order, or legal process under Zambian jurisdiction.</li>
            <li>To protect the rights, safety, or property of Venusnap, its users, or the public.</li>
        </ul>
    </section>

    <section id="data-storage">
        <h2>5. Data Storage and Security</h2>
        <p>We implement reasonable and appropriate security measures to protect your personal data against unauthorized access, disclosure, alteration, or destruction. Data is stored on secure servers, and access is restricted to authorized personnel.</p>
    </section>

     <section id="your-rights">
        <h2>6. Your Rights</h2>
        <p>Under the Zambian Data Protection Act, you have the right to:</p>

        <ul>
            <li>Access your personal data.</li>
            <li>Request correction or deletion of inaccurate data.</li>
            <li>Object to processing under certain circumstances.</li>
            <li>Withdraw consent at any time (without affecting past processing).</li>
        </ul>
        <p class="highlight">To exercise any of these rights, contact us at <a href="mailto:legal@venusnap.com">legal@venusnap.com</a></p>
    </section>

     <section id="retention-data">
        <h2>7. Retention of Data</h2>
        <p>We retain your personal data only as long as necessary to fulfill the purposes outlined in this policy, or as required by law.</p>
    </section>

    <section id="childrens">
        <h2>8. Children's Privacy</h2>
        <p>Venusnap is not intended for children under the age of 13. We do not knowingly collect personal data from individuals under 13. If we become aware of such collection, we will delete it immediately.</p>
    </section>

    <section id="changes">
        <h2>9. Changes to This Privacy Policy</h2>
        <p>We may update this policy from time to time. If significant changes are made, we will notify users via the platform or email.</p>
    </section>

    <!-- Additional sections would continue here -->

    <section id="contact">
        <h2>10. Contact Us</h2>
        <p>If you have any questions about this privacy policy or our data practices:</p>

        <div class="contact-card">
            <p><strong>Data Protection Officer</strong><br>
            Email: legal@venusnap.com<br>
            Address: 444 Alaska Avenue, California, Suite #CEC468</p>
        </div>
    </section>

    <div class="update-notice">
        <h3>Updates to This Policy</h3>
        <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Effective Date" at the top.</p>
        <p>You are advised to review this Privacy Policy periodically for any changes.</p>
    </div>
</body>
</html>

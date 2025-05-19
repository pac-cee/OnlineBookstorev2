# Security Policy

---

## 1. Reporting a Vulnerability
- If you discover a security issue, please email the maintainer or open a private issue.
- Do not disclose vulnerabilities publicly until they are resolved.

## 2. Security Practices
- All passwords are hashed before storage (never plain text).
- SQL injection is prevented via prepared statements.
- XSS is mitigated by escaping all user-generated content.
- File uploads are sanitized and restricted by type.
- Sessions are managed securely; session IDs are regenerated on login.
- Sensitive configuration is stored outside web root or in environment variables.

## 3. Dependencies
- Use trusted packages only; keep dependencies up to date.
- Monitor for security advisories and patch promptly.

## 4. Responsible Disclosure
- We appreciate responsible disclosure and will respond promptly to all reports.

---

For more details, see [DEVELOPER_README.md](DEVELOPER_README.md) and [SYSTEM_DESIGN.md](SYSTEM_DESIGN.md).

# Security Policy

Thank you for taking the time to review the security of **maatify/data-repository**.  
We take security very seriously and appreciate any responsible disclosures that help improve the safety and reliability of the Maatify ecosystem.

---

# 🔐 Supported Versions

Only the following versions currently receive security updates:

| Version | Status                |
|---------|-----------------------|
| 1.x     | ✔ Active Support      |
| < 1.0   | ❌ No longer supported |

If you are using an unsupported version, we strongly recommend upgrading to the latest stable release.

---

# 🚨 Reporting a Vulnerability

If you discover a security vulnerability, **DO NOT** open a public GitHub issue.

Instead, please contact the Maatify security team directly:

📧 **security@maatify.dev**

Provide as much detail as possible, including:

- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Affected versions
- Any proof-of-concept or references
- Optional: Suggested fix

You will receive an acknowledgment within **48 hours**, and we will follow up as needed for clarification.

---

# 🛠 Handling Process

Once a vulnerability is reported:

1. The Maatify team reviews and verifies the issue.
2. A fix is prepared in a private branch.
3. Maintainers coordinate with the reporter if more details are required.
4. A security patch is released (e.g., `1.x.x` patch version).
5. The vulnerability is documented in the changelog.
6. Credit is given to the reporter (optional and with consent).

---

# 🎯 Expectations for Responsible Disclosure

- Do not publish or share the vulnerability before a fix is released.
- Do not attempt to access user data or escalate the issue beyond proof-of-concept.
- Never perform attacks on production systems.

We appreciate all researchers who follow these guidelines to ensure a safe and reliable ecosystem.

---

# 🛡️ Scope

This policy applies specifically to:

- `maatify/data-repository`
- All repository drivers implemented under this package
- Repository resolver system
- DTO/hydration pipeline
- Query normalization layer

For vulnerabilities in other Maatify libraries, please refer to their respective security policies.

---

# 🙏 Thank You

Your efforts help keep the Maatify ecosystem secure and reliable for everyone.

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a></sub>
</p>

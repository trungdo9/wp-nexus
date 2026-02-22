# Security Audit Knowledge Base

Specialized knowledge for cybersecurity audits, vulnerability assessment, and security hardening.

## When to Use

Use this skill when:
- Performing security code reviews
- Assessing vulnerability risks
- Implementing security controls
- Reviewing authentication/authorization
- Checking supply chain security
- Conducting penetration testing
- Evaluating configuration security

---

## OWASP Top 10:2025

| Rank | Category | Description | Key Checks |
|------|----------|-------------|------------|
| **A01** | Broken Access Control | Authorization bypass, IDOR, SSRF | ✅ Role checks, ✅ Resource isolation |
| **A02** | Security Misconfiguration | Cloud configs, headers, defaults | ✅ Config audit, ✅ Header review |
| **A03** | Software Supply Chain | Dependencies, CI/CD, lock files | ✅ SBOM, ✅ Dependency audit |
| **A04** | Cryptographic Failures | Weak crypto, exposed secrets | ✅ TLS config, ✅ Secret management |
| **A05** | Injection | SQL, command, XSS patterns | ✅ Input validation, ✅ Sanitization |
| **A06** | Insecure Design | Architecture flaws, threat modeling | ✅ Design review, ✅ Risk analysis |
| **A07** | Authentication Failures | Sessions, MFA, credential handling | ✅ Auth flows, ✅ MFA enforcement |
| **A08** | Integrity Failures | Unsigned updates, tampered data | ✅ Signing verification, ✅ Checksums |
| **A09** | Logging & Alerting | Blind spots, insufficient monitoring | ✅ Log coverage, ✅ Alert rules |
| **A10** | Exceptional Conditions | Error handling, fail-open states | ✅ Error handling, ✅ Fail-secure |

---

## Vulnerability Scanner Guidelines

### Common Vulnerabilities

#### Injection Vulnerabilities

| Type | Pattern | Risk | Fix |
|------|---------|------|-----|
| **SQL Injection** | String concatenation in queries | Critical | Parameterized queries, ORM |
| **Command Injection** | `eval()`, `exec()`, `exec()` | Critical | Avoid dynamic execution |
| **XSS** | `dangerouslySetInnerHTML`, unsafe innerHTML | High | Output encoding, CSP |
| **LDAP Injection** | Unsanitized LDAP queries | High | Input validation, escaping |
| **XXE** | XML external entity parsing | Critical | Disable XXE, use safe parsers |

#### Authentication Issues

| Issue | Pattern | Risk | Fix |
|-------|---------|------|-----|
| **Weak Passwords** | No min length, complexity | High | Enforce strong password policy |
| **Missing MFA** | No 2FA option | Critical | Implement MFA |
| **Session Fixation** | Session ID not regenerated | Medium | Regenerate on login |
| **Insecure Session** | Long expiry, no timeout | Medium | Short expiry, idle timeout |
| **Credential Stuffing** | No rate limiting | High | Rate limiting, captcha |

#### Sensitive Data Exposure

| Issue | Pattern | Risk | Fix |
|-------|---------|------|-----|
| **Hardcoded Secrets** | API keys in code | Critical | Environment variables, secrets manager |
| **Logging Secrets** | Passwords in logs | High | Sanitize logs |
| **Cleartext Transmission** | HTTP instead of HTTPS | Critical | Enforce TLS |
| **Insufficient Encryption** | Weak algorithms (MD5, SHA1) | High | Use strong algorithms |
| **Missing Encryption** | Plaintext storage | Critical | Encrypt sensitive data |

---

## Red Team Tactics

### Reconnaissance Techniques

- Open source intelligence (OSINT)
- Social engineering vectors
- Network mapping
- Port scanning analysis
- Subdomain enumeration

### Attack Vectors

- **Web Attacks**: SQLi, XSS, CSRF, SSRF
- **API Attacks**: Broken Object Level Authorization
- **Infrastructure**: Misconfigurations, exposed services
- **Supply Chain**: Dependency confusion, typosquatting
- **Social Engineering**: Phishing, pretexting

### Defense Bypass Techniques

| Attack Type | Bypass Method |
|-------------|---------------|
| WAF | Encoding, fragmentation |
| Authentication | Password spraying, credential stuffing |
| Authorization | Parameter manipulation, IDOR |
| Input Validation | Type confusion, null bytes |

---

## Supply Chain Security (A03)

### Dependency Checks

| Check | Why It Matters |
|-------|----------------|
| **Lock files present** | Ensures reproducible builds |
| **Dependency audit** | Identifies known CVEs |
| **Pin versions** | Prevents supply chain attacks |
| **SBOM generated** | Visibility into components |
| **Signed commits** | Verifies code origin |
| **Audit dependencies** | Checks for malicious packages |

### CI/CD Security

- ✅ Use secrets scanning
- ✅ Enforce branch protection
- ✅ Require code review
- ✅ Validate pipeline security
- ✅ Monitor for anomalies
- ✅ Audit third-party actions

---

## Risk Prioritization Framework

### CVSS Scoring

| Severity | CVSS Score | Action |
|----------|------------|--------|
| **Critical** | 9.0 - 10.0 | Immediate fix (< 24h) |
| **High** | 7.0 - 8.9 | Fix within 1 week |
| **Medium** | 4.0 - 6.9 | Fix within 1 month |
| **Low** | 0.1 - 3.9 | Plan for next cycle |
| **Info** | 0.0 | Document only |

### EPSS (Exploit Prediction Scoring)

```
EPSS > 0.5 (50%+) → HIGH PRIORITY (actively exploited)
EPSS < 0.1 (10%-) → Lower priority
```

---

## Security Headers Checklist

| Header | Purpose | Recommended Value |
|--------|---------|-------------------|
| `Strict-Transport-Security` | HSTS | `max-age=31536000; includeSubDomains` |
| `Content-Security-Policy` | CSP | `default-src 'self'` |
| `X-Content-Type-Options` | MIME sniffing | `nosniff` |
| `X-Frame-Options` | Clickjacking | `DENY` or `SAMEORIGIN` |
| `Referrer-Policy` | Referrer leakage | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | Feature controls | Restrictive defaults |

---

## Common Security Anti-Patterns

| ❌ Don't Do | ✅ Do Instead |
|------------|---------------|
| Use `eval()` for dynamic code | Use safe parsing, AST |
| Concatenate SQL strings | Use parameterized queries |
| Store secrets in code | Use environment variables/secrets manager |
| Disable SSL verification | Always verify certificates |
| Use weak crypto (MD5, SHA1) | Use SHA-256+, Argon2, bcrypt |
| Skip input validation | Validate, sanitize all inputs |
| Log sensitive data | Sanitize logs, exclude secrets |
| Ignore security warnings | Address all security findings |
| Use default credentials | Enforce unique, strong credentials |
| Allow unlimited uploads | Validate file types, limit size |

---

## Security Review Checklist

### Authentication Review
- [ ] Strong password policy enforced
- [ ] MFA available and encouraged
- [ ] Session management secure
- [ ] Password reset flow secure
- [ ] Account lockout after failures
- [ ] No sensitive data in URLs

### Authorization Review
- [ ] Role-based access control
- [ ] Resource-level authorization
- [ ] No IDOR vulnerabilities
- [ ] Privilege escalation prevented
- [ ] Audit logging enabled

### Data Protection Review
- [ ] Sensitive data encrypted at rest
- [ ] TLS 1.2+ enforced
- [ ] No sensitive data in logs
- [ ] PII properly handled (GDPR compliance)
- [ ] Data retention policy defined

### Infrastructure Review
- [ ] Security headers present
- [ ] Unnecessary ports closed
- [ ] Cloud configs reviewed
- [ ] Container security verified
- [ ] Secrets properly managed

---

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [CWE Common Weakness Enumeration](https://cwe.mitre.org/)
- [NVD National Vulnerability Database](https://nvd.nist.gov/)
- [CVE Common Vulnerabilities and Exposures](https://cve.mitre.org/)

---

## Quick Reference

### Security Audit Formula

```
Security = Authentication + Authorization + Encryption + Validation + Monitoring + Hardening
```

### Zero Trust Principles

```
1. Never trust, always verify
2. Assume breach
3. Least privilege access
4. Defense in depth
5. Fail secure
```

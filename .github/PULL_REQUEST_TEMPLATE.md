# Pull Request

## Summary

<!-- One sentence. What does this PR change and why is it needed? -->

## Type of change

- [ ] Bug fix (non-breaking)
- [ ] New feature (non-breaking, additive)
- [ ] Breaking change (requires major version bump)
- [ ] Docs / tests only
- [ ] Dependency update

## Checklist

- [ ] `composer test:unit` is clean.
- [ ] `composer stan` passes (PHPStan level 8).
- [ ] `composer cs:check` passes (PHP CS Fixer dry-run).
- [ ] I updated `CHANGELOG.md` under `Unreleased` if this is a user-visible change.
- [ ] If the auth test vector was touched, the regression still matches the Java and .NET SDKs (ARCHITECTURE.md §3).
- [ ] No new Composer packages added without discussion.
- [ ] I did NOT commit credentials, tokens or sandbox payloads (`*.env` is gitignored).

## Cross-SDK parity

- [ ] This change is PHP-only.
- [ ] This change should also be mirrored in `checkout-java` and `checkout-dotnet`.
       <!-- Link sibling PRs or open issues: -->

## Testing

<!--
How did you verify this works? Include:
  - the command you ran
  - output snippets if behavior changed
  - sandbox output from the SandboxSmokeTest if you ran it against the real gateway
-->

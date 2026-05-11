# Releasing

Docify releases are driven by Git tags. Packagist reads Composer package versions from tags, so a tag like `v1.0.0` becomes the installable `1.0.0` release.

## Release Checklist

1. Make sure `main` contains the code you want to release.
2. Run the test suite locally:

    ```bash
    composer test
    ```

3. Choose the next version using semantic versioning:

    - Patch: `v1.0.1` for bug fixes.
    - Minor: `v1.1.0` for backwards-compatible features.
    - Major: `v2.0.0` for breaking changes.

4. Create and push the tag:

    ```bash
    git checkout main
    git pull
    git tag v1.0.0
    git push origin v1.0.0
    ```

5. GitHub Actions will validate the package, run tests, and create a GitHub Release with generated release notes.
6. Confirm Packagist shows the new version. If it does not update automatically, check that the package is connected to GitHub on Packagist.

If a bad release tag is pushed, delete the tag locally and remotely before creating the corrected tag:

```bash
git tag -d v1.0.0
git push origin :refs/tags/v1.0.0
```

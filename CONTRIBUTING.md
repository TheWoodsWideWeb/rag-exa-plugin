# Contributing to AI Trainer Dashboard

Thank you for your interest in contributing to the AI Trainer Dashboard plugin! This document provides guidelines and information for contributors.

## Table of Contents

- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Code Standards](#code-standards)
- [Pull Request Process](#pull-request-process)
- [Bug Reports](#bug-reports)
- [Feature Requests](#feature-requests)
- [Security Issues](#security-issues)
- [Testing](#testing)
- [Documentation](#documentation)
- [Release Process](#release-process)

## Getting Started

### Prerequisites

Before contributing, ensure you have:

- **PHP 7.4+** installed
- **WordPress 5.0+** development environment
- **Node.js 14+** and **npm** for frontend development
- **Composer** for PHP dependencies
- **Git** for version control
- **API Keys** for OpenAI and Exa.ai (for testing)

### Required Skills

- WordPress plugin development
- PHP programming
- JavaScript/ES6
- CSS/SCSS
- Database design and optimization
- API integration
- Security best practices
- Testing methodologies

## Development Setup

### 1. Clone the Repository

```bash
git clone https://github.com/samnguyen92/rag-exa-plugin.git
cd rag-exa-plugin
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration

Create a `.env` file in the root directory:

```env
# OpenAI API Configuration
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_EMBEDDING_MODEL=text-embedding-ada-002

# Exa.ai API Configuration
EXA_API_KEY=your_exa_api_key_here
EXA_BASE_URL=https://api.exa.ai

# WordPress Configuration
WP_DEBUG=true
WP_DEBUG_LOG=true
```

### 4. Database Setup

The plugin will automatically create required database tables upon activation. Ensure your WordPress database is properly configured.

### 5. Build Assets

```bash
# Development mode with watch
npm run start

# Production build
npm run build
```

## Code Standards

### PHP Standards

Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

- Use **PSR-4** autoloading
- Follow **WordPress naming conventions**
- Use **WordPress functions** when available
- Include **proper documentation**
- Follow **security best practices**

#### Example PHP Code Structure

```php
<?php
/**
 * Function Description
 *
 * @since 1.1.0
 * @param string $param1 Description of parameter
 * @param int    $param2 Description of parameter
 * @return array|WP_Error Array of results or WP_Error on failure
 */
function ai_trainer_example_function( $param1, $param2 = 0 ) {
    // Input validation
    if ( empty( $param1 ) ) {
        return new WP_Error( 'invalid_param', __( 'Parameter cannot be empty', 'ai-trainer' ) );
    }

    // Sanitize inputs
    $param1 = sanitize_text_field( $param1 );
    $param2 = absint( $param2 );

    // Perform operation
    $result = perform_operation( $param1, $param2 );

    // Return result
    return $result;
}
```

### JavaScript Standards

Follow modern JavaScript standards:

- Use **ES6+** features
- Follow **WordPress JavaScript standards**
- Use **proper error handling**
- Include **JSDoc documentation**

#### Example JavaScript Code Structure

```javascript
/**
 * Example JavaScript function
 * 
 * @param {string} param1 - Description of parameter
 * @param {number} param2 - Description of parameter
 * @returns {Promise<Object>} Promise resolving to result object
 */
async function exampleFunction(param1, param2 = 0) {
    try {
        // Input validation
        if (!param1) {
            throw new Error('Parameter cannot be empty');
        }

        // Perform operation
        const result = await performOperation(param1, param2);

        return result;
    } catch (error) {
        console.error('Error in exampleFunction:', error);
        throw error;
    }
}
```

### CSS/SCSS Standards

Follow modern CSS practices:

- Use **SCSS** for better organization
- Follow **BEM methodology**
- Use **CSS custom properties**
- Ensure **responsive design**

#### Example SCSS Structure

```scss
// Variables
$primary-color: #007cba;
$secondary-color: #005a87;
$border-radius: 4px;

// Mixins
@mixin button-style($bg-color, $text-color: white) {
    background-color: $bg-color;
    color: $text-color;
    border: none;
    border-radius: $border-radius;
    padding: 8px 16px;
    cursor: pointer;
    
    &:hover {
        background-color: darken($bg-color, 10%);
    }
}

// Component styles
.ai-trainer {
    &__button {
        @include button-style($primary-color);
        
        &--secondary {
            @include button-style($secondary-color);
        }
    }
}
```

## Pull Request Process

### 1. Create a Feature Branch

```bash
git checkout -b feature/your-feature-name
```

### 2. Make Your Changes

- Write clear, documented code
- Include proper error handling
- Add tests for new functionality
- Update documentation

### 3. Test Your Changes

```bash
# Run PHP tests
composer test

# Run JavaScript tests
npm test

# Run linting
npm run lint:js
npm run lint:css
```

### 4. Commit Your Changes

Use conventional commit messages:

```bash
git commit -m "feat: add new feature description"
git commit -m "fix: resolve issue description"
git commit -m "docs: update documentation"
git commit -m "style: improve code formatting"
git commit -m "refactor: restructure code"
git commit -m "test: add test coverage"
```

### 5. Push and Create Pull Request

```bash
git push origin feature/your-feature-name
```

Create a pull request with:

- **Clear title** describing the change
- **Detailed description** of what was changed
- **Testing instructions** for reviewers
- **Screenshots** if UI changes were made
- **Related issues** if applicable

### 6. Code Review

All pull requests require:

- **Code review** from maintainers
- **Passing tests** and linting
- **Documentation updates**
- **Security review** for sensitive changes

## Bug Reports

### Before Reporting

1. **Check existing issues** for duplicates
2. **Test with latest version**
3. **Disable other plugins** to isolate the issue
4. **Check browser console** for JavaScript errors
5. **Review WordPress debug log**

### Bug Report Template

```markdown
**Bug Description**
Clear description of the bug

**Steps to Reproduce**
1. Step 1
2. Step 2
3. Step 3

**Expected Behavior**
What should happen

**Actual Behavior**
What actually happens

**Environment**
- WordPress Version: X.X.X
- Plugin Version: X.X.X
- PHP Version: X.X.X
- Browser: X.X.X
- Theme: X.X.X

**Additional Information**
- Screenshots if applicable
- Error messages
- Console logs
```

## Feature Requests

### Before Requesting

1. **Check existing features** to avoid duplicates
2. **Consider the scope** and complexity
3. **Think about use cases** and benefits
4. **Research similar features** in other plugins

### Feature Request Template

```markdown
**Feature Description**
Clear description of the requested feature

**Use Case**
Why this feature is needed

**Proposed Implementation**
How you think it should work

**Benefits**
What benefits this feature would provide

**Alternative Solutions**
Other ways to achieve the same goal
```

## Security Issues

### Reporting Security Issues

**DO NOT** create public issues for security vulnerabilities. Instead:

1. **Email** security@psychedelic.com
2. **Include detailed information** about the vulnerability
3. **Provide steps to reproduce** if possible
4. **Wait for acknowledgment** before public disclosure

### Security Guidelines

- **Never commit API keys** or sensitive data
- **Use WordPress nonces** for form submissions
- **Sanitize all inputs** and escape outputs
- **Validate file uploads** properly
- **Use prepared statements** for database queries
- **Follow WordPress security best practices**

## Testing

### Testing Requirements

All code changes must include:

- **Unit tests** for new functions
- **Integration tests** for API interactions
- **Frontend tests** for JavaScript functionality
- **Manual testing** for user workflows

### Running Tests

```bash
# PHP tests
composer test

# JavaScript tests
npm test

# E2E tests (if applicable)
npm run test:e2e
```

### Test Coverage

- **Minimum 80%** code coverage
- **Critical functions** must have 100% coverage
- **API endpoints** must have comprehensive tests
- **User workflows** must be tested

## Documentation

### Documentation Requirements

All contributions must include:

- **Inline code comments** for complex logic
- **Function documentation** with PHPDoc/JSDoc
- **README updates** for new features
- **API documentation** for new endpoints
- **User guide updates** for new functionality

### Documentation Standards

- Use **clear, concise language**
- Include **code examples**
- Provide **screenshots** for UI changes
- Follow **consistent formatting**
- Keep **up-to-date** with code changes

## Release Process

### Version Numbers

Follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible new features
- **PATCH** version for backwards-compatible bug fixes

### Release Checklist

Before each release:

- [ ] **All tests pass**
- [ ] **Documentation updated**
- [ ] **Changelog updated**
- [ ] **Version numbers updated**
- [ ] **Security review completed**
- [ ] **Performance testing completed**
- [ ] **Compatibility testing completed**

### Release Steps

1. **Create release branch**
2. **Update version numbers**
3. **Update changelog**
4. **Run full test suite**
5. **Create release tag**
6. **Deploy to production**
7. **Announce release**

## Getting Help

### Resources

- **WordPress Developer Handbook**: https://developer.wordpress.org/
- **Plugin Development Guide**: https://developer.wordpress.org/plugins/
- **Coding Standards**: https://developer.wordpress.org/coding-standards/
- **Security Guidelines**: https://developer.wordpress.org/plugins/security/

### Community

- **GitHub Issues**: For bug reports and feature requests
- **WordPress.org Forums**: For general WordPress questions
- **Stack Overflow**: For technical questions
- **Discord/Slack**: For real-time discussions

## Recognition

Contributors will be recognized in:

- **README.md** contributors section
- **Changelog** for significant contributions
- **Release notes** for major features
- **Plugin credits** for substantial contributions

## Code of Conduct

All contributors must follow our [Code of Conduct](CODE_OF_CONDUCT.md), which promotes:

- **Respectful communication**
- **Inclusive environment**
- **Professional behavior**
- **Constructive feedback**

## License

By contributing to this project, you agree that your contributions will be licensed under the same license as the project (GPL-2.0-or-later).

---

Thank you for contributing to the AI Trainer Dashboard plugin! Your contributions help make this project better for everyone.

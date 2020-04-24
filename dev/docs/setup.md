# `tric` Setup

## Prerequisites

1. Docker

## Setup

1. Clone this repository to your machine
1. Ensure the `tric` command is runnable by adding the `dev/` directory to your path or symlinking `tric` into a directory that is in your path.

### Add to your path

Edit your `.bashrc` or `.zshrc` or equivalent file in your home directory and add:

```bash
export PATH=$PATH:/PATH/TO/products-test-automation/dev

# An example entry in .zshrc is where this repo is cloned to ~/git/products-test-automation:
# export PATH=$PATH:$HOME/git/products-test-automation/dev
```

### Symlink `tric`

```bash
cd /usr/local/bin
ln -s /PATH/TO/products-test-automation/dev/tric
```

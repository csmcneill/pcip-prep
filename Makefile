PLUGIN_SLUG := pcip-prep
VERSION     := $(shell grep -m1 "Version:" pcip-prep.php | sed 's/.*Version:[[:space:]]*//')
DIST_DIR    := dist
ZIP_FILE    := $(DIST_DIR)/$(PLUGIN_SLUG)-$(VERSION).zip
STAGE_DIR   := $(DIST_DIR)/.stage/$(PLUGIN_SLUG)

.PHONY: build clean

build: clean
	@echo "Building $(PLUGIN_SLUG) v$(VERSION)..."
	@mkdir -p $(STAGE_DIR)
	@rsync -a --exclude='.git' --exclude='.github' --exclude='.gitignore' \
		--exclude='Makefile' --exclude='.DS_Store' --exclude='*.bak' \
		--exclude='dist' ./ $(STAGE_DIR)/
	@cd $(DIST_DIR)/.stage && zip -rq $(CURDIR)/$(ZIP_FILE) $(PLUGIN_SLUG)/
	@rm -rf $(DIST_DIR)/.stage
	@echo "Created $(ZIP_FILE)"

clean:
	@rm -rf $(DIST_DIR)

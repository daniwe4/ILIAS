#!/bin/bash

# This file is part of ILIAS, a powerful learning management system
# published by ILIAS open source e-Learning e.V.
#
# ILIAS is licensed with the GPL-3.0,
# see https://www.gnu.org/licenses/gpl-3.0.en.html
# You should have received a copy of said license along with the
# source code, too.
#
# If this is not the case or you just want to try ILIAS, you'll find
# us at:
# https://www.ilias.de
# https://github.com/ILIAS-eLearning
#
# Build and deploy style specific files.

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[${NOW}] Building style folder."

source "./CI/Style-To-Repo/build.sh"

source "./CI/Style-To-Repo/deploy.sh"

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[${NOW}] Cleanup build and deploy artifacts."
source "./CI/Style-To-Repo/cleanup.sh"
removeBuildArtifacts
removeDeployArtifacts

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[${NOW}] Done"
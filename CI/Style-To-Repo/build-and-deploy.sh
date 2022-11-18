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

echo ${PR_NUMBER}
echo ${PR_}
echo ${PR_REPO}
echo ${HEAD_COMMIT_MSG}
echo ${HEAD_COMMIT_ID}
echo ${HEAD_COMMIT_URL}
echo ${GITHUB_REF_NAME}
exit
source "./CI/Style-To-Repo/build.sh"
source "./CI/Style-To-Repo/deploy.sh"
source "./CI/Style-To-Repo/cleanup.sh"

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[${NOW}] Building style folder."
build

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[${NOW}] Deploy style folder."
deploy

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[${NOW}] Cleanup build and deploy artifacts."
removeBuildArtifacts
removeDeployArtifacts

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[${NOW}] Done"
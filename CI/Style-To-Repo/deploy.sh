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
# This script compares the actual style repo with the built style folder and pushes the possible changes to repo.

REPO="git@github.com:daniwe4/style_test.git"
NOW=$(date +'%d.%m.%Y %I:%M:%S')
BASE_FOLDER="./CI/Style-To-Repo/repo"

if [ -d ${BASE_FOLDER} ]
then
  rm -rf ${BASE_FOLDER}
fi

mkdir -p ${BASE_FOLDER}

git clone ${REPO} ${BASE_FOLDER} >/dev/null 2>&1

rm -rf ${BASE_FOLDER}/*

cp -r CI/Style-To-Repo/style/* ${BASE_FOLDER}

git -C ${BASE_FOLDER} update-index --really-refresh >/dev/null 2>&1
git -C ${BASE_FOLDER} diff-index --quiet HEAD

CHECK=$?
if [[ "${CHECK}" == "0" ]]
then
  echo "[${NOW}] No changes detected on style files."
else
  echo "[${NOW}] Detect changes on style files. They will be committed to ${REPO}"
  git -C ${BASE_FOLDER} add . >/dev/null 2>&1
  git -C ${BASE_FOLDER} commit -m "[${NOW}] Detect changes on style files." >/dev/null 2>&1
  git -C ${BASE_FOLDER} push origin master >/dev/null 2>&1
fi
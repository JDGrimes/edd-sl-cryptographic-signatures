#!/usr/bin/env bash

if [[ -z $1 ]]; then
	echo "Usage: bin/sign-package.sh path/to/package-name.zip"
	exit
fi

package=$1
signed="${package}.minisig"

# Sign the package (this will prompt for the secret key password).
minisign -Sm "${package}"

# Convert the signature from base64 to hex, and trim extra data added by minisign.
signature=$(sed -n 2p "${signed}" | base64 -D | xxd -c 148 -p | cut -c21-)

# Copy the signature to the clipboard.
echo -n "${signature}" | pbcopy

# Remove the .minisig signature file, we don't need it.
rm "${signed}"

echo 'File signature (copied to clipboard):'
echo
echo "${signature}"
echo

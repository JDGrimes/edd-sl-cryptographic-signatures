# EDD SL Cryptographic Signatures [![Build Status](https://travis-ci.org/JDGrimes/edd-sl-cryptographic-signatures.svg?branch=develop)](https://travis-ci.org/JDGrimes/edd-sl-cryptographic-signatures)

This WordPress plugin is an extension for the Easy Digital Downloads plugin, and integrates with its Software Licenses extension to provide cryptographic signatures for download packages.

## Purpose

In order to use this plugin, you should understand the basics of [digital signatures](https://paragonie.com/blog/2015/08/you-wouldnt-base64-a-password-cryptography-decoded#digital-signatures). Cryptographic signatures [are not produced via hash functions](https://paragonie.com/blog/2015/08/you-wouldnt-base64-a-password-cryptography-decoded#download-verification), but by digitally signing a package with a private key. A digital signature ensures both integrity and authenticity. When a package is verified using a digital signature and the public key, it proves that the package's contents are exactly what the owner of the private key intended.

Why is this important?

Say that one day, your EDD site was hacked. The attacker could put out new updates for all of your downloads, and by including a backdoor in the packages, he could compromise all of the sites running your downloads. In other words, every site that installs your downloads is only as secure as your own site and update infrastructure. If your site, or the network connection between sites, is compromised, then the other site is placed at risk.

But it doesn't have to be this way. You can ensure that the security of your site doesn't affect the sites of those who install your downloads. You can protect against infrastructure attacks by digitally signing your packages. Then, you can include code in your downloads to verify an update package with the signature and public key before installing it. If things don't match up, then the package did not come from you—it wasn't signed by your private key. And in that case, your download can refuse to install the update.

Now if an attacker compromises your site, any package updates they put out won't be installed by the sites running your downloads. The attacker won't have your private key, and so won't be able to provide valid signatures for the packages.

Now you can have peace of mind!

You just need to make sure that you always sign your packages offline; never ever upload the private key to your server, or else a hacker would be able to get it, defeating the purpose.

## Set Up

So the steps to use this plugin are:

1. Generate a private/public key pair using Ed25519. One easy way to do this is using [Minisign](https://jedisct1.github.io/minisign/):

   ```
   $ minisign -G
   ```
1. Add code to verify update packages to the update code for your downloads, and include your public key.
1. Use your private key to digitally sign your packages for future updates (a [helper script](https://github.com/JDGrimes/edd-sl-cryptographic-signatures/blob/develop/bin/sign-package.sh) is provided for this purpose):

   ```
   $ bin/sign-package.sh my-download.zip
   ```
    ...and supply the signatures to the update API using this plugin (see below).


## Screenshots

You can enter the signature for each file in the Download Files meta box:

![screenshot-2017-11-7 edit download wordpress develop wordpress](https://user-images.githubusercontent.com/4005415/32516039-f6e83c3a-c3cf-11e7-9eb1-20e9b76da469.png)

Then, the signature for the download package is included in the API response:

![src wordpress-develop dev_edd_action get_version item_id 317_-_2017-11-07_15 18 15](https://user-images.githubusercontent.com/4005415/32516074-128927b0-c3d0-11e7-8ec8-c143f8d96437.png)

This also works when beta versions are enabled: the `ed25519_signature` key will be the signature of the beta package.

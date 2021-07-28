# KBART Export Plugin

### Description

This plugin exports all journals of an OJS instance including metadata as ```.txt``` file in KBART format. This file can be downloaded from [http://serv29.ub.uni-heidelberg.de/ojs-next/index.php/index/kbartexport](http://serv29.ub.uni-heidelberg.de/ojs-next/index.php/index/kbartexport).

The file name is given by
```
[ProviderName]_[Region/Consortium]_[PackageName]_[YYYY-MM-DD].txt
```
and can be adjusted in `KBARTExportHandler.inc.php`.

The table is listed in alphabetical order by journal title, the columns are separated by tabulators. According to the guidelines of KBART the following informations are included:
1. Title,
2. URL,
3. Print ISSN,
4. Online ISSN,
5. Publisher's institution.

Further information can be found at [https://service-wiki.hbz-nrw.de/pages/viewpage.action?pageId=470024321](https://service-wiki.hbz-nrw.de/pages/viewpage.action?pageId=470024321).

### Installation

Copy the repository to your OJS plugin's folder
```
cd <your_ojs_installation>/plugins/generic
git clone https://gitlab.ub.uni-heidelberg.de/sf409/kbartexport.git
```
Go to `Settings > Website > Plugins` and activate the plugin.

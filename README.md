# KBART Export Plugin

The KBART Export Plugin enables exporting metadata of all journals hosted in a given OJS instance as `.txt` file in KBART format. The file name is of the form of
```
[ProviderName]_[Region/Consortium]_[PackageName]_[YYYY-MM-DD].txt
```
and can be configured in the plugin's settings.

Further information concerning KBART in general can be found at https://service-wiki.hbz-nrw.de/pages/viewpage.action?pageId=470024321 and the links therein.

## Installation
```plaintext
git clone https://gitlab.ub.uni-heidelberg.de/sf409/kbartexport.git kbartExport
```

## System Requirements
This plugin is compatible with
- OJS 3.2,
- OMP 3.3,
- OMP 3.4


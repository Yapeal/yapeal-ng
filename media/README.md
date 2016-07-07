# Instructions for make PNGs

Here are the commands use to make to PNGs from the SVG files.

```sh
cd media
inkscape --without-gui --file=yapeal-ng_banner.svg -b#808080 -y.5 --export-png=yapeal-ng_banner.png
inkscape --without-gui --file=yapeal-ng_banner.svg -b#808080 -y.5 -w1456 -h180 --export-png=Yapeal-ng_twitter_banner.png
inkscape --without-gui --file=Yapeal-ng_logo.svg -b#808080 -y.5 --export-png=Yapeal-ng_logo.png
inkscape --without-gui --file=Yapeal-ng_logo.svg -w75 -h75 --export-png=Yapeal-ng_icon.png
```

Afterwards I processed them through https://tinypng.com/

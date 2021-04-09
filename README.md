# xExtension-RedditImage
A FreshRSS extension to process Reddit feeds

If the the link in the content is recognized, the content is replaced by the linked
resource (image or video). If the link in the content is not recognized, the link
used in the title is modified to link to the content resource instead of the reddit
comment page.

At the moment, only the following resources are recognized:

&nbsp; |match | type | support
-------|------|------|--------
1 | links finished by jpg, png, gif, bmp | image | full
2 | imgur links finished by gifv | video | full
3 | imgur links finished with a token | image | partial
4 | links finished by webm, mp4 | video | full
5 | gfycat links finished with a token | video | full
6 | redgifs links finished with a token | video | full
7 | reddit links finished with a token | video | limited
8 | reddit image galleries | image | limited

### Configuration

Item | Detail | Default
-----|--------|--------
Media height | Select a media height in viewport percentage | 70%
Muted video | Choose if videos are muted or not | True
Display images | Choose if images are displayed | True
Display videos | Choose if videos are displayed | True
Display original content | Choose if original contents are displayed | True
Display metadata | Choose if original content metadata are displayed | False

**Note:**
When the *display original content* option is set to *true*, text content will be displayed twice. Once from the extracted content and once from the original content. To have a nicer interface, it is recommended to set that option to *false*.

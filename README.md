# Grav Multi-File-Folder Plugin

The **multifilefolder plugin** for [Grav](http://github.com/getgrav/grav) allows you to use folders that contain many markdown files, without having to create a subfolder for each page.

![](assets/screenshot.png)

# Installation

Shouldn't use this unless you are really familiar with grav.  Install it by downloading zip and use it like any other plugin.

# Configuration

Only `enable` option true/false.

## CACHE
Turn system cache OFF in the system config as you install and try it out. But, after you have your bugs worked out, Then cache on, it works fine for me.

# Why

I have lots of personal sites that use markdown files in a structure of folders with many files in each folder.

I don't want to change the structure, or their filenames just to use GRAV. I want to keep those md files in the same structure.

# Demo

The screenshot above is for my blog at [http://www.jhinline.com](http://www.jhinline.com).

# Page Requirements

## Template
Since these pages are not named based on the template you want to use, such as `blog.md` or `item.md`, you need to add a `template` frontmatter variable. The easier way is to use a file called `frontmatter.yaml` in the same folder. My frontmatter.yaml looks like this:

~~~~
title: 'No Title Provided by Page'
template: 'blog-item'
~~~~

So, I don't really have to change my old markdown files.  Also, I get a placeholder title that lets me know I still have to edit the file and provide a title.

## What is 0index.md?

Grav by default only finds the **FIRST md file in any folder**. That is the only page that is routable and viewable from the folder.  Since, the multifilefolder system has many md files in the folder, you want to force which file is the first one grav finds. This is considered the default file for that folder.  In other CMSes  the default file in a folder was index.md; but, here, that won;t be the first file found.  So name it `0index.md` to force it to be the default for grav.   The rest of the files will be loaded by this **jacmultifilefolder plugin.**
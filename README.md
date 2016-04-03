# ttm41135

Source code on server apache/htdocs/site

**Pull new changes:**
- `cd apache/htdocs/site`
- `git pull origin master`

**Push new changes:**
- First, pull the latest version of master to avoid mergeconflicts: `git pull origin master`
- Make sure that you only stage files you have changed: `git status` 
- Stage changed files for commit: `git add -A`
- Commit changes: `git commit -m "Commit message"`
- Push changes: `git push origin master`

If you are not able to push (the update is rejected) try `git pull -r origin master`. This will pull down any changes in master and rebase/place your commits on top.

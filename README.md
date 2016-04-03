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

**Test your work:**

In order to have a stable master all new features should be tested in a new branch on the server. How to do this you say?

- Create a new branch: `git checkout -b your-branch`
- Do your work and commit and push the changes. There will now be a new branch on the remote (github)
- Log into the server and go to site folder: `cd apache/htdocs/site`
- Pull changes for your branch `git pull origin your-branch`
- Checkout your branch to test the solution: `git checkout your-branch`
- Restart apache: `apachectl stop` and `apachectl start`
- See your code fail!
- REMEMBER! Switch back to master when you are done! `git checkout master` and restart apache. 

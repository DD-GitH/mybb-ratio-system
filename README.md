# mybb-ratio-system
Ratio Plugin for MyBB 1.8

Explanations :
The ratio is a number returned by the devision "Threads / Posts". The administrator sets a minimum ratio amount and if the member has a ratio less than this amount, he can't reply to threads and he will be redirected to another page which informs him that. He needs to increase his threads number to get a higher ratio.
Such plugin is a good solution to reduce leech on forums and maybe useful for other stuffs, especially when the forum  runs the hide until reply plugin.

Notes :
For more forum management & performance :
- The administrator can set forums where the members can reply freely and the ratio won't be affected by this forum's posts/threads (useful for some sections, for example : suggestions section, or help section). 
- He can set an infinite ratio to some usergroups (Useful for paid groups for example).
- Ratio won't be affected by replying to own thread, so members can reply to questions on their threads...etc

Debug :
You may get a small problem if you're using a custom theme which have different "member_profile" template. So if you want to add the ratio somewhere in your "member_profile" template, just write {$ratio} to show the current ratio of the member in his profile.

Languages :
French & English (already included in the plugin files)

Website : https://developement.design/
Skype : ef.team
Email : business@developement.design (business only, not support)

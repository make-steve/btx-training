function WriteBlogPost(val)
{
	if(val)
	{
		document.getElementById('microblog-link').style.display = "none";
		document.getElementById('microblog-form').style.display = "block";
		BX.onCustomEvent(BX('microblog-form'), 'onFormShow');
	}
	else
	{
		document.getElementById('microblog-link').style.display = "block";
		document.getElementById('microblog-form').style.display = "none";
	}
}
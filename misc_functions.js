function is_valid_number(num)
{
	if(parseFloat(num)<0)
	{
		num = Math.abs(parseFloat(num));
	}
	numberPattern = /^\d+\.?\d{0,10}$/;
	return numberPattern.test(num);
}

function is_empty(s)
{
	return s == '';
}
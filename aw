-- LocalScript
-- Put in StarterPlayerScripts

local Players = game:GetService("Players")
local TweenService = game:GetService("TweenService")

local player = Players.LocalPlayer

local centerCFrame = CFrame.new(
	-8353.04883, 482.494202, 1468.88794,
	1, 0, 0,
	0, 1, 0,
	0, 0, 1
)

local tweenToPointTime = 4 -- tween there in 4 seconds
local walkTweenTime = 1.2 -- not too fast
local respawnWaitTime = 0.7 -- wait when teleported back to spawn

local walkOffset = Vector3.new(3, 0, 0)
local resetDistance = 80 -- if you get sent far away, it counts as teleported back

local currentTween
local busy = false

local function getHRP()
	local character = player.Character or player.CharacterAdded:Wait()
	return character:WaitForChild("HumanoidRootPart")
end

local function tweenTo(hrp, cf, time)
	if currentTween then
		currentTween:Cancel()
	end

	currentTween = TweenService:Create(
		hrp,
		TweenInfo.new(time, Enum.EasingStyle.Sine, Enum.EasingDirection.InOut),
		{ CFrame = cf }
	)

	currentTween:Play()
	currentTween.Completed:Wait()
end

local function isFarFromPoint(hrp)
	return (hrp.Position - centerCFrame.Position).Magnitude > resetDistance
end

local function mainLoop()
	local hrp = getHRP()

	while true do
		busy = true

		-- If you got teleported back to spawn or far away, wait .7 first
		if isFarFromPoint(hrp) then
			task.wait(respawnWaitTime)
		end

		-- Tween to the main point in 4 seconds
		tweenTo(hrp, centerCFrame, tweenToPointTime)

		local pointA = centerCFrame
		local pointB = centerCFrame + walkOffset

		-- Walk back and forth until the game sends you away
		while not isFarFromPoint(hrp) do
			tweenTo(hrp, pointB, walkTweenTime)
			if isFarFromPoint(hrp) then break end

			tweenTo(hrp, pointA, walkTweenTime)
			if isFarFromPoint(hrp) then break end

			task.wait(0.05)
		end

		busy = false
		task.wait(0.05)
	end
end

task.spawn(mainLoop)

player.CharacterAdded:Connect(function()
	task.wait(respawnWaitTime)
	if currentTween then
		currentTween:Cancel()
	end
end)
